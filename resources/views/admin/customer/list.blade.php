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
                    <h1 class="card-title col-sm-6">Customers</h1>
                    <span class="col-sm-6 text-right">
                        <a href="{{ route('coaster-commerce.admin.customer.add') }}" class="btn btn-success">
                            <i class="fa fa-user"></i> &nbsp; Add New
                        </a>
                    </span>
                </div>

                <table class="table table-bordered" id="customerList"></table>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        function ccEdit() {
            return {
                display: function(data) {
                    return '<a href="' + ccRouter.route('coaster-commerce.admin.customer.edit', {id: data}) + '">Edit</a>';
                },
            };
        }
        $(document).ready(function() {

            let dt = $('#customerList').DataTable({
                pageLength: 25,
                scrollX: true,
                responsive: true,
                columns: [
                    {data:'id',title:'ID'},
                    {data:'name',title:'Contact Name'},
                    {data:'company',title:'Company'},
                    {data:'group',title:'Group'},
                    {data:'email',title:'Email'},
                    {data:'country',title:'Country'},
                    {data:'last_login',title:'Last Login',type:'datetime'},
                    {data:'created_at',title:'Created At',type:'datetime'},
                    {data:'id',title:'Edit',searchable:false,render:ccEdit()},
                ],
                ajax: '{{ route('coaster-commerce.api.customer.admin-list') }}'
            });

        });
    </script>
@append
