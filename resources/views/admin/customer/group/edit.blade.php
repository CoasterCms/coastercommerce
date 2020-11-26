<?php
/**
 * @var CoasterCommerce\Core\Model\Customer\Group $group
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.customer.group.save', ['id' => $group->exists ? $group->id : 0]) }}" method="post">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            @if ($group->exists)
                                Edit {{ $group->name }}
                            @else
                                New Customer Group
                            @endif
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            @if ($group->exists)
                                <a href="{{ route('coaster-commerce.admin.customer.group.delete', ['id' => $group->id]) }}" class="btn btn-danger confirm mb-2" data-confirm="you wish to delete this group">
                                    <i class="fas fa-trash-alt"></i> &nbsp; Delete
                                </a> &nbsp;
                            @endif
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button> &nbsp;
                            <button name="saveAction" value="return" class="btn btn-success mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Save & return to group list
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
                            </ul>
                        </div>

                        <div class="col-md-10">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    {!! (new Attribute('name', 'text', 'Name'))->renderInput($group->name) !!}
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

        });
    </script>
@append