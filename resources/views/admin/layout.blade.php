<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <title>{{ $title }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ config('coaster-commerce.url.assets') }}/_/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ config('coaster-commerce.url.assets') }}/datatables/datatables.min.css" rel="stylesheet">
    <link href="{{ config('coaster-commerce.url.assets') }}/select2/select2.min.css" rel="stylesheet">
    <link href="{{ config('coaster-commerce.url.assets') }}/fileinput/css/fileinput.min.css" rel="stylesheet">
    <link href="{{ config('coaster-commerce.url.assets') }}/datetime/jquery.datetimepicker.min.css" rel="stylesheet">
    <link href="{{ config('coaster-commerce.url.assets') }}/_/css/styles.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito&display=swap" rel="stylesheet">
    <script src="{{ config('coaster-commerce.url.assets') }}/_/js/fa-all.min.js"></script>

</head>

<body>

{!! app(\CoasterCommerce\Core\Menu\AdminMenu::class)->render() !!}

<div class="mainwrap">

    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand-md fixed-top bg-light navbar-light">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <!--
                <form class="form-inline mt-2 mt-md-0">
                    <input class="form-control mr-sm-2" type="text" placeholder="Search" aria-label="Search">
                    <button class="btn btn-primary my-2 my-sm-0" type="submit">Search</button>
                </form>
                -->
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item"> <a class="nav-link" href="/" target="_blank">Open Frontend</a> </li>
                    @if ($coasterCmsLink = config('coaster::admin.url'))
                    <li class="nav-item"> <a class="nav-link" href="/{{ $coasterCmsLink }}">Back to Coaster Admin</a> </li>
                    <li class="nav-item"> <a class="nav-link" href="/{{ $coasterCmsLink }}/logout">Logout</a> </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div id="commerceAlerts" class="col-12">
                    <div class="alert" id="commerceAlert" style="display: none;">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                </div>
                <div class="col-12" id="maincontent">
                    {!! $content !!}
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-light">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-12  text-right">
                    <p>&copy; All rights reserved Coaster CMS {{ date('Y') }}</p>
                </div>
            </div>
        </div>
    </footer>

</div>

<script src="{{ config('coaster-commerce.url.assets') }}/_/js/jquery-3.4.1.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/_/js/bootstrap.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/datatables/datatables.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/select2/select2.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/fileinput/js/sortable.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/fileinput/js/popper.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/fileinput/js/fileinput.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/tinymce/tinymce.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/datetime/jquery.datetimepicker.full.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/sortable/Sortable.min.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/_/js/cc-router.js"></script>
<script src="{{ config('coaster-commerce.url.assets') }}/_/js/scripts-admin.js"></script>

<script type="text/javascript">
    ccRouter.addRoutes({!! $ccRoutes !!});
    ccRouter.setBase('/');
</script>

@yield('scripts')
<script type="text/javascript">
@foreach($alerts as $alertClass => $alertArray)
    @foreach($alertArray as $alert)
        commerceAlert('{{ $alertClass }}', '{{ $alert }}');
    @endforeach
@endforeach
</script>

</body>
</html>