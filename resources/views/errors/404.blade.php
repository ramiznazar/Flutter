<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>404 - Page Not Found | Crutox Admin</title>
        <meta content="Page Not Found" name="description" />

        <link rel="shortcut icon" href="{{ asset('assets/admin/images/favicon.ico') }}">

        <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/admin/css/icons.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/admin/css/style.css') }}" rel="stylesheet" type="text/css">

        <style>
            .error-page {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .error-content {
                text-align: center;
                color: #fff;
                padding: 40px 20px;
            }
            .error-code {
                font-size: 120px;
                font-weight: 700;
                line-height: 1;
                margin-bottom: 20px;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }
            .error-title {
                font-size: 36px;
                font-weight: 600;
                margin-bottom: 15px;
            }
            .error-message {
                font-size: 18px;
                margin-bottom: 30px;
                opacity: 0.9;
            }
            .error-actions {
                margin-top: 40px;
            }
            .error-actions .btn {
                margin: 0 10px;
                padding: 12px 30px;
                font-size: 16px;
                border-radius: 5px;
            }
            .error-icon {
                font-size: 80px;
                margin-bottom: 30px;
                opacity: 0.8;
            }
        </style>
    </head>

    <body class="fixed-left">
        <div class="error-page">
            <div class="error-content">
                <div class="error-icon">
                    <i class="mdi mdi-alert-circle-outline"></i>
                </div>
                <div class="error-code">404</div>
                <h1 class="error-title">Page Not Found</h1>
                <p class="error-message">
                    The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
                </p>
                <div class="error-actions">
                    <a href="{{ url('/admin/dashboard') }}" class="btn btn-light waves-effect waves-light">
                        <i class="mdi mdi-home"></i> Go to Dashboard
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-light waves-effect waves-light">
                        <i class="mdi mdi-arrow-left"></i> Go Back
                    </a>
                </div>
            </div>
        </div>

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
        <script src="{{ asset('assets/admin/js/app.js') }}"></script>
    </body>
</html>

