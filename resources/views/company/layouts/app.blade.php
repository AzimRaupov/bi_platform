<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title')</title>
     @include('company.partials.default_head')
</head>
<body>
<a href="#content" class="visually-hidden skip-link">Skip to main content</a>

<script src="{{asset('dist/js/tabler-theme.min.js?1779974228')}}"></script>

<div class="page">
    @include('company.partials.header')
    @include('company.partials.nav')
    <div class="page-wrapper">


        <!-- END PAGE HEADER -->
        <!-- END PAGE HEADER -->
        @yield('page_header')

        <!-- BEGIN PAGE BODY -->
        <main id="content" class="page-body">
            <div class="container-fluid px-">

        @yield('content')


            </div>
        </main>
        <!-- END PAGE BODY -->
        <!-- BEGIN FOOTER -->
        <!--  BEGIN FOOTER  -->
         @include('company.partials.footer')
         <!--  END FOOTER  -->
        <!-- END FOOTER -->
    </div>
</div>

@include('company.partials.dash_create_modal')

@include('company.partials.theme_settings')

@include('company.partials.scripts')

</body>
</html>
