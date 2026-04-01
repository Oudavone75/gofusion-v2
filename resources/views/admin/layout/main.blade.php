<!DOCTYPE html>
<html>

<head>
    <!-- Basic Page Info -->
    <meta charset="utf-8">
    <title>Admin - @yield('title')</title>

    <!-- Site favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vendors/images/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vendors/images/favicon-32x32.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vendors/images/favicon-16x16.ico') }}">

    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/core.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/icon-font.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/datatables/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/datatables/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/sweetalert2/sweetalert2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/common.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/search.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/export-modal.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/styles/role-permissions.css') }}">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- switchery css -->
	<link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/switchery/switchery.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/fancybox/dist/jquery.fancybox.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('src/plugins/dropzone/src/dropzone.css') }}">

    @stack('styles')

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-119386393-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-119386393-1');
    </script>
</head>

<body>

    {{--start loader--}}
    @include('admin.layout.loader')
    {{--end loader--}}
    {{--start header--}}
    @include('admin.layout.header')
    {{--end header--}}

    {{--start sidebar--}}
    @include('admin.layout.sidebar')
    {{--end sidebar--}}
    <div class="main-container vh-100">
        @yield('content')
        {{-- @include('admin.layout.footer') --}}
    </div>
    <!-- js -->
    <script src="{{ asset('vendors/scripts/core.js') }}"></script>
    <script src="{{ asset('vendors/scripts/script.min.js') }}"></script>
    <script src="{{ asset('vendors/scripts/process.js') }}"></script>
    <script src="{{ asset('vendors/scripts/layout-settings.js') }}"></script>
    <script src="{{ asset('vendors/scripts/custom.js') }}"></script>
    <script src="{{ asset('vendors/scripts/quiz-export.js') }}"></script>
    <script src="{{ asset('vendors/scripts/role-permissions.js') }}"></script>
    <script src="{{ asset('src/plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('src/plugins/datatables/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('vendors/scripts/toastr-messages.js') }}"></script>
    <!-- switchery js -->
	<script src="{{ asset('src/plugins/switchery/switchery.min.js') }}"></script>
	<script src="{{ asset('vendors/scripts/advanced-components.js') }}"></script>
    <!-- add sweet alert js & css in footer -->
    {{-- <script src="{{ asset('src/plugins/sweetalert2/sweetalert2.all.js') }}"></script> --}}
    {{-- <script src="{{ asset('src/plugins/sweetalert2/sweet-alert.init.js') }}"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- fancybox Popup Js -->
	<script src="{{ asset('src/plugins/fancybox/dist/jquery.fancybox.js') }}"></script>
	<script src="{{ asset('src/plugins/dropzone/src/dropzone.js') }}"></script>
    <script src="{{ asset('vendors/scripts/export-modal.js') }}"></script>
    @stack('scripts')
</body>

</html>
