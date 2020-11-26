<?php
/**
 * @var CoasterCommerce\Core\Model\Customer[] $customers
 */
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row mb-4">
                    <h1 class="card-title col-sm-6">Orders</h1>
                    <span class="col-sm-6 text-right">
                        <button id="showFilters" class="btn btn-info mr-3">
                            <i class="fa fa-filter"></i> &nbsp; Show Filters
                        </button>
                    </span>
                </div>

                <form action="{{ route('coaster-commerce.api.product.admin-list') }}" id="filterForm" class="d-none">
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label for="filterstatus">
                                Order Status
                            </label>
                            <select id="filterstatus" name="filters[order_status][]" class="form-control select2" multiple>
                                @foreach($statuses as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 text-right mt-3">
                            <button class="btn btn-info" id="clearFilters" type="button">
                                Clear Filters
                            </button>
                            <button class="btn btn-success">
                                <span class="sync-ready">
                                    <i class="fa fa-filter"></i> &nbsp; Filter Orders
                                </span>
                                <span class="sync-progress d-none">
                                    <i class="fa fa-spinner fa-pulse"></i> &nbsp; Filtering
                                </span>
                            </button>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered" id="orderList"></table>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            let dt = $('#orderList').DataTable({
                pageLength: 25,
                scrollX: true,
                responsive: true,
                order: [[1, 'desc']],
                columns: [
                    {data:'id',title:'ID'},
                    {data:'number',title:'Order Number'},
                    {data:'email',title:'Email'},
                    {data:'name',title:'Name'},
                    {data:'total',title:'Total (Inc. Vat)',type:'price'},
                    {data:'date_placed',title:'Placed On'},
                    {data:'paid',title:'Payment'},
                    {data:'status',title:'Status'},
                    {data:'view',title:'View',searchable:false},
                ],
                ajax: '{{ route('coaster-commerce.api.order.admin-list') }}',
                createdRow: function (row, data, index) {
                    if (data['status_colour']) {
                        $(row).css('background', data['status_colour']);
                    }
                }
            });

            let filterForm = $('#filterForm');

            filterForm.on('submit', function (e) {
                e.preventDefault();

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

                $.get('{{ route('coaster-commerce.api.order.admin-list') }}', formInputs, function(r) {
                    iconFilter.removeClass('d-none');
                    iconSpinner.addClass('d-none');
                    for (let disableInput of disableInputs) {
                        disableInput.attr('disabled', false);
                    }
                    dt.clear().rows.add(r.data).draw();
                });

            });

            $('#clearFilters').click(function() {
                filterForm.find('input').val('');
                filterForm.find('select').val('');
                filterForm.find('.select2').trigger('change');
            });

            $('#showFilters').click(function () {
                filterForm.toggleClass('d-none');
                $(this).html(filterForm.hasClass('d-none') ? 'Show Filters' : 'Hide Filters');
            });

        });
    </script>
@append
