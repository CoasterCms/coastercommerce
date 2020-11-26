<?php
/**
 * @var CoasterCommerce\Core\Model\Product\Attribute $attribute
 * @var CoasterCommerce\Core\Model\Product\Attribute\GroupItem $groupItem
 * @var array $groups
 * @var array inputTypes
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
// TODO meta length opts ??
// TODO variation attribute filter/column ??
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.attribute.save', ['id' => $attribute->exists ? $attribute->id : 0]) }}" method="post">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            @if ($attribute->exists)
                                Edit {{ $attribute->name }}
                            @else
                                New Attribute
                            @endif
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            @if ($attribute->exists && !$attribute->isSystem())
                                <a href="{{ route('coaster-commerce.admin.attribute.delete', ['id' => $attribute->id]) }}" class="btn btn-danger confirm mb-2" data-confirm="you wish to delete this attribute">
                                    <i class="fas fa-trash-alt"></i> &nbsp; Delete
                                </a> &nbsp;
                            @endif
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button> &nbsp;
                            <button name="saveAction" value="return" class="btn btn-success mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Save & return to attribute list
                            </button>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-2 mb-3">
                            <ul class="nav nav-pills flex-column" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="home" aria-selected="true">
                                        General
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="admin-manage-tab" data-toggle="tab" href="#admin-manage" role="tab" aria-controls="home" aria-selected="true">
                                        Field Management
                                    </a>
                                </li>
                                <li class="nav-item d-none" id="input-options-item">
                                    <a class="nav-link" id="input-options-tab" data-toggle="tab" href="#input-options" role="tab" aria-controls="home" aria-selected="true">
                                        Input Options
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-10">
                            <div class="tab-content">

                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    {!! (new Attribute('name', 'text', 'Name'))->renderInput($attribute->name) !!}
                                    {!! (new Attribute('code', 'text', 'Code'))->renderInput($attribute->code, ['disabled' => $attribute->isSystem()]) !!}
                                    {!! (new Attribute('search_weight', 'text', 'Search Weight'))->renderInput($attribute->search_weight) !!}
                                    {!! (new Attribute('search_filter', 'switch', 'Search Filter'))->renderInput($attribute->search_filter) !!}
                                </div>

                                <div class="tab-pane fade show" id="admin-manage" role="tabpanel" aria-labelledby="admin-manage-tab">
                                    {!! (new Attribute('admin_filter', 'text', 'List Filter Order'))->renderInput($attribute->admin_filter) !!}
                                    {!! (new Attribute('admin_column', 'text', 'List Column Order'))->renderInput($attribute->admin_column) !!}
                                    {!! (new Attribute('id', 'select', 'Edit Group'))->key('attribute-group')->renderInput($groupItem->group_id, ['options' => $groups]) !!}
                                    {!! (new Attribute('position', 'text', 'Edit Group Position'))->key('attribute-group')->renderInput($groupItem->position) !!}
                                    {!! (new Attribute('frontend', 'select', 'Edit Input Type'))->renderInput($attribute->frontend, ['options' => $inputTypes, 'disabled' => $attribute->isSystem()]) !!}
                                    {!! (new Attribute('datatype', 'select', 'Edit Datatype'))->renderInput($attribute->getDataType() ?: 'string', ['options' => $dataTypes, 'note' => 'if changing datatype you will have to manually convert and existing values in database to the new type', 'disabled' => $attribute->isSystem()]) !!}
                                    {!! (new Attribute('admin_validation', 'text', 'Edit Validation Rules'))->renderInput($attribute->admin_validation) !!}
                                </div>

                                <div class="tab-pane fade show" id="input-options" role="tabpanel" aria-labelledby="options-tab">
                                    <!-- select opts / length guide -->
                                </div>

                            </div>
                        </div>

                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            let firstError = $('.is-invalid').first();
            if (firstError) {
                $('#' + firstError.closest('.tab-pane').attr('id') + '-tab').click();
            }

            let metaData = {
                @foreach($attribute->meta->pluck('value', 'key')->toArray() as $metaKey => $metValue)
                "{{ $metaKey }}" : @if (in_array($metaKey, ['options'])) {!! $metValue !!} @else "{{ $metValue }}" @endif ,
                @endforeach
            };

            let selectOptionsIndex = 0;

            $('#attributes_frontend').change(function () {
                let recommendedDatatypes = {
                    'text': 'string',
                    'number': 'float',
                    'textarea': 'text',
                    'wysiwyg': 'text',
                    'select': 'string',
                    'select-multiple': 'string',
                    'date': 'datetime',
                    'switch': 'integer',
                    'price': 'decimal',
                    'gallery': 'text',
                    'variation_attributes': 'text'
                };
                updateInputTypes($(this).val(), $(this).attr('disabled') === 'disabled');
                $('#attributes_datatype').val(recommendedDatatypes[$(this).val()]).trigger('change');
            }).trigger('change');

            function updateInputTypes(inputType, disabled) {
                let optionsNavItem = $('#input-options-item');
                let inputOptionsContent = $('#input-options');
                if (inputType.substring(0, 6) === 'select') {
                    inputOptionsContent.html(selectInputOptionContent());
                    selectInputOptionListeners();
                    optionsNavItem.removeClass('d-none');
                    return;
                }
                inputOptionsContent.html('');
                optionsNavItem.addClass('d-none');
            }

            function selectInputOptionContent() {
                metaData.options = metaData.hasOwnProperty('options') ? metaData.options : [];
                let inputOptionsHtml = '<p>List of values this attribute can be set to.  </p>' +
                    '<table class="table table-hover" id="attributeOptionValuesTable"><thead><tr><th>Admin Name</th><th>Frontend Value</th><th></th></tr></thead><tbody>';
                let optionsLength = metaData.options.length;
                selectOptionsIndex = 0;
                for (let i = 0; i < optionsLength; i++) {
                    inputOptionsHtml += selectInputOptionRow(metaData.options[i]);
                }
                inputOptionsHtml += '</tbody></table><button class="btn btn-info" type="button" id="addAttributeOption">Add Option</button>';
                return inputOptionsHtml;
            }

            function selectInputOptionListeners() {
                $('#attributeOptionValuesTable').on('keyup', 'input', function () {
                    let trRow = $(this).closest('tr');
                    metaData.options[trRow.index()][$(this).data('field')] = $(this).val();
                });
                $('#addAttributeOption').click(function () {
                    metaData.options.push({name:'',value:''});
                    $('#attributeOptionValuesTable tbody').append(selectInputOptionRow(metaData.options[metaData.options.length - 1]));
                });
                $('#attributeOptionValuesTable ').on('click', '.fa-trash', function () {
                    let trRow = $(this).closest('tr');
                    metaData.options.splice(trRow.index(), 1);
                    trRow.remove();
                });
            }

            function selectInputOptionRow(option) {
                selectOptionsIndex++;
                let optionValue = option.name === option.value ? '' : option.value;
                return '<tr>' +
                    '<td><input type="text" name="options[' + selectOptionsIndex + '][name]" value="' + option.name + '" data-field="name" class="w-100" /></td>' +
                    '<td><input type="text" name="options[' + selectOptionsIndex + '][value]" value="' + optionValue + '" data-field="value" class="w-100" placeholder="same as name" /></td>' +
                    '<td><a href="javascript:void(0)"><i class="fa fa-trash"></i></a></td>' +
                    '</tr>';
            }

        });
    </script>

@append