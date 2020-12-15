<?php
/**
 * @var Role $role
 * @var Action[] $actions
 * @var array $permissions
 * @var array $roleOptions
 * @var array $userOptions
 */
use CoasterCommerce\Core\Renderer\Admin\Attribute;
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <form action="{{ route('coaster-commerce.admin.permission.save', ['id' => $role->exists ? $role->id : 0]) }}" method="post">

                    {!! csrf_field() !!}

                    <div class="row">

                        <h1 class="card-title col-sm-7 mb-5">
                            @if ($role->exists)
                                Edit {{ $role->name }}
                            @else
                                New Role
                            @endif
                        </h1>

                        <div class="col-sm-5 mb-5 text-right">
                            @if ($role->exists)
                                <a href="{{ route('coaster-commerce.admin.permission.delete', ['id' => $role->id]) }}" class="btn btn-danger confirm mb-2" data-confirm="you wish to delete this role">
                                    <i class="fas fa-trash-alt"></i> &nbsp; Delete
                                </a> &nbsp;
                            @endif
                            <button name="saveAction" value="continue" class="btn btn-success mb-2">
                                <i class="fas fa-save"></i> &nbsp; Save
                            </button> &nbsp;
                            <button name="saveAction" value="return" class="btn btn-success mb-2">
                                <i class="fas fa-arrow-circle-left"></i> &nbsp; Save & return to role list
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
                                    <a class="nav-link" id="permissions-tab" data-toggle="tab" href="#permissions" role="tab" aria-controls="home" aria-selected="true">
                                        Permissions
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-10">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    {!! (new Attribute('label', 'text', 'Name'))->renderInput($role->label) !!}
                                    {!! (new Attribute('role_id', 'select', 'Connect To Cms Role'))->renderInput($role->role_id, ['options' => $roleOptions]) !!}
                                    {!! (new Attribute('user_id', 'select', 'Or connect To User'))->renderInput($role->user_id, ['options' => $userOptions]) !!}
                                </div>
                                <div class="tab-pane fade" id="permissions" role="tabpanel" aria-labelledby="permissions-tab">
                                    @foreach($actions->sortBy(['display_group', 'id']) as $action)
                                        @if (isset($displayGroup) && $displayGroup != $action->display_group)
                                            <br />
                                        @endif
                                        {!! (new Attribute($action->id, 'switch', $action->label))->key('permission')->renderInput(array_key_exists($action->id, $permissions)) !!}
                                        @php $displayGroup = $action->display_group; @endphp
                                    @endforeach
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
