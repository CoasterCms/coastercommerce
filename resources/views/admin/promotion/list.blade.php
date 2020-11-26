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
                    <h1 class="card-title col-sm-6">Promotions</h1>
                    <span class="col-sm-6 text-right">
                        <a href="{{ route('coaster-commerce.admin.promotion.add') }}" class="btn btn-success">
                            <i class="fa fa-tag"></i> &nbsp; Add New
                        </a>
                    </span>
                </div>

                <table class="table table-bordered" id="promotionList"></table>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            let dt = $('#promotionList').DataTable({
                pageLength: 25,
                scrollX: true,
                responsive: true,
                order: [[1, 'desc']],
                columns: [
                    {data:'id',title:'ID'},
                    {data:'enabled',title:'Enabled'},
                    {data:'type',title:'Type'},
                    {data:'name',title:'Name'},
                    {data:'active',title:'Active'},
                    {data:'customer',title:'Applies to Customers / Groups'},
                    {data:'discount',title:'Discount'},
                    {data:'edit',title:'Edit',searchable:false},
                ],
                ajax: '{{ route('coaster-commerce.api.promotion.admin-list') }}'
            });

        });
    </script>
@append
