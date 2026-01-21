<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta name="referrer" content="no-referrer" />
        <title>{{ $pageTitle ?? 'Crutox Admin Dashboard' }}</title>
        <meta content="Admin Dashboard" name="description" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <link rel="shortcut icon" href="{{ asset('assets/admin/images/favicon.ico') }}">

        @if(isset($extraCSS) && is_array($extraCSS))
            @foreach($extraCSS as $css)
                <link href="{{ asset($css) }}" rel="stylesheet" type="text/css">
            @endforeach
        @endif

        <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/admin/css/icons.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/admin/css/style.css') }}" rel="stylesheet" type="text/css">

        <style>
            /* Reduce gap between sidebar icons and text */
            #sidebar-menu > ul > li > a > i {
                margin-right: 2px !important;
            }
        </style>

        @stack('styles')
    </head>

    <body class="fixed-left">

        <!-- Loader -->
        <div id="preloader"><div id="status"><div class="spinner"></div></div></div>

        <!-- Begin page -->
        <div id="wrapper">

            @include('admin.includes.sidebar')

            <!-- Start right Content here -->
            <div class="content-page">
                <!-- Start content -->
                <div class="content">

                    @include('admin.includes.header')

                    <div class="page-content-wrapper ">

                        <div class="container-fluid">

                            @if(session('message'))
                            <div class="alert alert-{{ session('messageType', 'info') }} alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif

                            @yield('content')

                        </div><!-- container -->

                    </div> <!-- Page content Wrapper -->

                </div> <!-- content -->

                @include('admin.includes.footer')

            </div>
            <!-- End Right content here -->

        </div>
        <!-- END wrapper -->

        <!-- jQuery  -->
        <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/admin/js/popper.min.js') }}"></script>
        <script src="{{ asset('assets/admin/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/admin/js/modernizr.min.js') }}"></script>
        <script src="{{ asset('assets/admin/js/detect.js') }}"></script>
        <script src="{{ asset('assets/admin/js/fastclick.js') }}"></script>
        <script src="{{ asset('assets/admin/js/jquery.blockUI.js') }}"></script>
        <script src="{{ asset('assets/admin/js/waves.js') }}"></script>
        <script src="{{ asset('assets/admin/js/jquery.nicescroll.js') }}"></script>
        
        <!-- Admin API Helper -->
        <script src="{{ asset('assets/admin/js/admin-api.js') }}"></script>

        @if(isset($extraScripts) && is_array($extraScripts))
            @foreach($extraScripts as $script)
                <script src="{{ asset($script) }}"></script>
            @endforeach
        @endif

        @stack('scripts')

        <!-- App js -->
        <script src="{{ asset('assets/admin/js/app.js') }}"></script>

    </body>
</html>

