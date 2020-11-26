
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <div class="row mb-4">
                    <h1 class="card-title col-sm-6">Abandoned Carts</h1>
                    <span class="col-sm-6 text-right">

                    </span>
                </div>

                <table class="table table-bordered" id="cartList"></table>

            </div>
        </div>
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function() {

            function ccView() {
                return {
                    display: function(data) {
                        return '<a href="' + ccRouter.route('coaster-commerce.admin.customer.abandoned-cart.view', {id: data}) + '">View</a>';
                    },
                };
            }

            let dt = $('#cartList').DataTable({
                pageLength: 25,
                order: [[2, 'desc']],
                scrollX: true,
                responsive: true,
                columns: [
                    {data:'id',title:'ID'},
                    {data:'email',title:'Email'},
                    {data:'date',title:'Date'},
                    {data:'status',title:'Status'},
                    {data:'items',title:'Items'},
                    {data:'total',title:'Total',type:'price'},
                    {data:'email_last_sent',title:'Email Last Sent'},
                    {data:'emails_sent',title:'Email Count'},
                    {data:'converted',title:'Converted ?'},
                    {data:'id',title:'View',orderable:false,searchable:false,render:ccView()},
                ],
                ajax: '{{ route('coaster-commerce.api.customer.abandoned-cart.admin-list') }}'
            });

        });
    </script>
@append
