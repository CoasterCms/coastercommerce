<?php
/**
 * @var Illuminate\Database\Eloquent\Collection $columnConf
 * @var bool $massActionsEnabled
 */
use \CoasterCommerce\Core\Model\Setting;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row mb-4">
                    <h1 class="card-title col-sm-6">Products</h1>
                    <span class="col-sm-6 text-right">
                        @if ($massActionsEnabled)
                        <button id="showMassActions" class="btn btn-warning mr-3">
                            <i class="fa fa-tasks"></i> &nbsp; <span>Mass Actions</span>
                        </button>
                        @endif
                        <button id="showFilters" class="btn btn-info mr-3">
                            <i class="fa fa-filter"></i> &nbsp; <span>Show Filters</span>
                        </button>
                        <a href="{{ route('coaster-commerce.admin.product.add') }}" class="btn btn-success">
                            <i class="fa fa-boxes"></i> &nbsp; Add New
                        </a>
                    </span>
                </div>

                <form action="{{ route('coaster-commerce.api.product.admin-list') }}" id="filterForm" class="d-none">
                    <div class="row">
                        @foreach($filterAttributes as $filterAttribute)
                            @if ($filterView = $filterAttribute->renderFilter())
                            <div class="col-12 col-lg-6 col-xl-4 mb-2">
                                {!! $filterView !!}
                            </div>
                            @endif
                        @endforeach
                        <div class="col-12 text-right mt-3">
                            <button class="btn btn-info" id="clearFilters" type="button">
                                Clear Filters
                            </button>
                            <button class="btn btn-success">
                                <span class="sync-ready">
                                    <i class="fa fa-filter"></i> &nbsp; Filter Products
                                </span>
                                <span class="sync-progress d-none">
                                    <i class="fa fa-spinner fa-pulse"></i> &nbsp; Filtering
                                </span>
                            </button>
                        </div>
                    </div>
                </form>

                <form action="{{ route('coaster-commerce.admin.product.mass-action') }}" method="POST" id="massActionsForm" class="d-none">
                    <div class="row">
                        <div class="col-sm-8 form-inline">
                            {!! csrf_field() !!}
                            <label for="mAction">Mass Action:</label>
                            <select name="action" id="mAction" class="form-control mx-3">
                                <option value="">Select Action</option>
                                <option value="delete">Delete Products</option>
                                <option value="update">Update Attributes</option>
                            </select>
                            <input type="hidden" name="ids" id="aProductIds" />
                            currently selected (<span class="aNumber">0</span>) products
                        </div>
                        <div class="col-sm-4 text-right">
                            <button class="btn btn-info select-items" data-type="all" type="button">
                                Select All
                            </button>
                            <button class="btn btn-info select-items" data-type="none" type="button">
                                Unselect All
                            </button>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered" id="productList"></table>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        function ccEdit() {
            return {
                display: function(data) {
                    return '<a href="' + ccRouter.route('coaster-commerce.admin.product.edit', {id: data}) + '">Edit</a>';
                },
            };
        }

        $(document).ready(function() {

            let filterForm = $('#filterForm');

            let dt = $('#productList').DataTable({
                select: {
                    info: false,
                    style: 'api'
                },
                sDom: "<'row'<'col-sm-12 col-md-6 dt-head-entries'li><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                pageLength: 25,
                scrollX: true,
                responsive: true,
                columns: {!! str_replace('"render":"cc-edit"', '"render":ccEdit()', $columnConf->toJson()) !!},
                ajax: {
                    'url': '{{ route('coaster-commerce.api.product.admin-list') }}',
                    'data': function (d) {
                        return $.extend({}, d, getFilterFormInput());
                    }
                },
                stateSave: true,
                stateSaveCallback: function(settings, data) {
                    $.post(ccRouter.route('coaster-commerce.api.table-state.load', {name: 'product_list'}), {value: JSON.stringify(data)});
                },
                stateLoadCallback: function(settings, callback) {
                    $.ajax({
                        url :ccRouter.route('coaster-commerce.api.table-state.save', {name: 'product_list'}),
                        dataType: 'json',
                        success: function(r) {
                            callback(JSON.parse(r.data));
                        }
                    });
                }
            });

            function getFilterFormInput() {
                let formInputs = {};
                $.each(filterForm.serializeArray(), function(i, field) {
                    if (field.name.substr(-2) === '[]') {
                        if (!formInputs[field.name]) {
                            formInputs[field.name] = [];
                        }
                        formInputs[field.name].push(field.value);
                    } else {
                        formInputs[field.name] = field.value;
                    }
                });
                return formInputs;
            }

            filterForm.on('submit', function (e) {
                e.preventDefault();

                let formInputs = getFilterFormInput();

                let iconSpinner = $(this).find('button > .sync-progress');
                let iconFilter = $(this).find('button > .sync-ready');
                let disableInputs = [
                    $(this).find('input'),
                    $(this).find('select'),
                    $(this).find('button')
                ];
                for (let disableInput of disableInputs) {
                    disableInput.attr('disabled', true);
                }
                iconFilter.addClass('d-none');
                iconSpinner.removeClass('d-none');

                $.get('{{ route('coaster-commerce.api.product.admin-list') }}', formInputs, function(r) {
                    iconFilter.removeClass('d-none');
                    iconSpinner.addClass('d-none');
                    for (let disableInput of disableInputs) {
                        disableInput.attr('disabled', false);
                    }
                    dt.clear().rows.add(r.data).draw();
                    dt.rows().deselect();
                });

            });

            let actionsForm = $('#massActionsForm');

            function updateActionsInfo() {
                actionsForm.find('.aNumber').html(dt.rows({selected: true}).count());
                $('#aProductIds').val(dt.rows({selected: true}).data().map(a => a.id).toArray().join(','));
            }
            dt.on('select', updateActionsInfo);
            dt.on('deselect', updateActionsInfo);

            actionsForm.find('.select-items').click(function () {
                if ($(this).data('type') === 'all') {
                    dt.rows({filter: 'applied'}).select();
                } else {
                    dt.rows({filter: 'applied'}).deselect();
                }
            });

            $('#mAction').change(function () {
                if ($(this).val() && $('#aProductIds').val()) {
                    @if (Setting::getValue('catalogue_redirect'))
                    if ($(this).val() === 'delete') {
                        actionsForm.attr('action', ccRouter.route('coaster-commerce.admin.product.redirect'));
                    }
                    @endif
                    actionsForm.submit();
                } else if ($(this).val()) {
                    $(this).val('');
                }
            });

            $('#clearFilters').click(function() {
                filterForm.find('input').val('');
                filterForm.find('select').val('');
                filterForm.find('.select2').trigger('change');
            });

            $('#showFilters').click(function () {
                filterForm.toggleClass('d-none');
                $(this).find('span').html(filterForm.hasClass('d-none') ? 'Show Filters' : 'Hide Filters');
            });

            $('#showMassActions').click(function () {
                actionsForm.toggleClass('d-none');
                $(dt.body()).attr('class', actionsForm.hasClass('d-none') ? '' : 'select-enabled');
                dt.select.style(actionsForm.hasClass('d-none') ? 'api' : 'multi');
                $(this).find('span').html(actionsForm.hasClass('d-none') ? 'Mass Actions' : 'Hide Actions');
            });

        });
    </script>
@append