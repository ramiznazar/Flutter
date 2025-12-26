<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>Crutox Admin - Login</title>
        <meta content="Admin Dashboard" name="description" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <link rel="shortcut icon" href="{{ asset('assets/admin/images/favicon.ico') }}">

        <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/admin/css/icons.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/admin/css/style.css') }}" rel="stylesheet" type="text/css">
    </head>

    <body>

    <!-- Begin page -->
    <div class="accountbg"></div>
    <div class="wrapper-page">

        <div class="card">
            <div class="card-body">

                <div class="text-center">
                    <a href="{{ route('admin.login') }}" class="logo logo-admin" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none;">
                        <img src="{{ asset('assets/admin/images/logo.png') }}" height="50" alt="Crutox Logo">
                        <span style="color: #333; font-size: 24px; font-weight: bold;">Crutox</span>
                    </a>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> {{ $errors->first() }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="px-3 pb-3">
                    <form class="form-horizontal m-t-20" method="POST" action="{{ route('admin.login') }}">
                        @csrf

                        <div class="form-group row">
                            <div class="col-12">
                                <input class="form-control" type="text" name="username" required placeholder="Username" value="{{ old('username') }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-12">
                                <div class="position-relative">
                                    <input class="form-control" type="password" name="password" id="password" required placeholder="Password" style="padding-right: 45px;">
                                    <button type="button" class="btn btn-link position-absolute" id="togglePassword" style="right: 0; top: 50%; transform: translateY(-50%); padding: 0.375rem 0.75rem; color: #6c757d; text-decoration: none; border: none; background: transparent; cursor: pointer;">
                                        <i class="mdi mdi-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-12">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="customCheck1" name="remember">
                                    <label class="custom-control-label" for="customCheck1">Remember me</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-center row m-t-20">
                            <div class="col-12">
                                <button class="btn btn-danger btn-block waves-effect waves-light" type="submit">Log In</button>
                            </div>
                        </div>

                        <div class="form-group m-t-10 mb-0 row">
                            <div class="col-12 m-t-20 text-center">
                                <small class="text-muted">Admin access only. Registration is disabled.</small>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

        <!-- jQuery  -->
        <script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
        <script src="{{ asset('assets/admin/js/popper.min.js') }}"></script>
        <script src="{{ asset('assets/admin/js/bootstrap.min.js') }}"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const togglePassword = document.getElementById('togglePassword');
                const passwordInput = document.getElementById('password');
                const eyeIcon = document.getElementById('eyeIcon');
                
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle eye icon
                    if (type === 'password') {
                        eyeIcon.classList.remove('mdi-eye-off');
                        eyeIcon.classList.add('mdi-eye');
                    } else {
                        eyeIcon.classList.remove('mdi-eye');
                        eyeIcon.classList.add('mdi-eye-off');
                    }
                });
            });
        </script>

    </body>
</html>

