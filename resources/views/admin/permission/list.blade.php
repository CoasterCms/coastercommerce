<?php
/**
 * @var \CoasterCommerce\Core\Model\Permission\Role[] $roles
 */
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row mb-4">
                    <h1 class="card-title col-sm-6">Permissions</h1>
                    <span class="col-sm-6 text-right">
                        <a href="{{ route('coaster-commerce.admin.permission.add') }}" class="btn btn-success">
                            <i class="fa fa-key"></i> &nbsp; Add Role
                        </a>
                    </span>
                </div>

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Role Name</th>
                        <th>Connected to CMS Role</th>
                        <th>Edit</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td>{{ $role->label }}</td>
                            <td>{{ $role->usedFor() }}</td>
                            <td><a href="{{ route('coaster-commerce.admin.permission.edit', ['id' => $role->id]) }}">Edit</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

