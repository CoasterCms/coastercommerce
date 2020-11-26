
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row mb-2">
                    <h1 class="card-title col-sm-6">Product Attributes</h1>
                    <span class="col-sm-6 text-right">
                        <a href="{{ route('coaster-commerce.admin.attribute.add') }}" class="btn btn-success">
                            <i class="fa fa-database"></i> &nbsp; Add New
                        </a>
                    </span>
                </div>

                <p>
                    Here you can add or remove product fields (attributes), they will need to be added to the design to display on the front of the site.<br />
                    Admin Filter & Admin Column can be used to modify the products listing page in the admin (value denotes the order it appears, 0 = hidden)<br />
                    System attributes can't be removed as they are required for products to work.
                </p>

                <table class="table table-bordered" id="attributeList"></table>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            let dt = $('#attributeList').DataTable({
                pageLength: 25,
                scrollX: true,
                responsive: true,
                columns: [
                    {data:'id',title:'ID'},
                    {data:'name',title:'Attribute Name'},
                    {data:'code',title:'Code'},
                    {data:'datatype',title:'Datatype'},
                    {data:'admin_filter',title:'Admin Filter'},
                    {data:'admin_column',title:'Admin Column'},
                    {data:'search_weight',title:'Search Weight'},
                    {data:'system',title:'System'},
                    {data:'edit',title:'Edit',searchable:false},
                ],
                ajax: '{{ route('coaster-commerce.api.attribute.admin-list') }}'
            });

        });
    </script>
@append
