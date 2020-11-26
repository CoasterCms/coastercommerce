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
                    <h1 class="card-title col-sm-6">Customer Groups</h1>
                    <span class="col-sm-6 text-right">
                        <a href="{{ route('coaster-commerce.admin.customer.group.add') }}" class="btn btn-success">
                            <i class="fa fa-users"></i> &nbsp; Add New
                        </a>
                    </span>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                            <tr>
                                <td>{{ $group->id }}</td>
                                <td>{{ $group->name }}</td>
                                <td><a href="{{ route('coaster-commerce.admin.customer.group.edit', ['id' => $group->id]) }}">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

