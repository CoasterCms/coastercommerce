
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row mb-4">
                    <h1 class="card-title col-sm-6">Redirects</h1>
                    <span class="col-sm-6 text-right">
                        <a href="{{ route('coaster-commerce.admin.redirect.add') }}" class="btn btn-success">
                            <i class="fa fa-map-signs"></i> &nbsp; Add New
                        </a>
                    </span>
                </div>

                <table class="table table-bordered" id="redirectList"></table>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            function ccEdit() {
                return {
                    display: function(data) {
                        return '<a href="' + ccRouter.route('coaster-commerce.admin.redirect.edit', {id: data}) + '">Edit</a>';
                    },
                };
            }

            function ccDelete() {
                return {
                    display: function(data) {
                        return '<a href="' + ccRouter.route('coaster-commerce.admin.redirect.delete', {id: data}) + '">Delete</a>';
                    },
                };
            }

            let dt = $('#redirectList').DataTable({
                pageLength: 25,
                scrollX: true,
                responsive: true,
                columns: [
                    {data:'id',title:'ID'},
                    {data:'url',title:'Url'},
                    {data:'redirects_to',title:'Redirects To'},
                    {data:'id',title:'Edit',orderable:false,searchable:false,render:ccEdit()},
                    {data:'id',title:'Delete',orderable:false,searchable:false,render:ccDelete()},
                ],
                ajax: '{{ route('coaster-commerce.api.redirect.admin-list') }}'
            });

        });
    </script>
@append
