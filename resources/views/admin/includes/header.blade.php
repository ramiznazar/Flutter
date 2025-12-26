<!-- Top Bar Start -->
<div class="topbar">

    <nav class="navbar-custom">

        <ul class="list-inline float-right mb-0">
            <!-- language-->
            <li class="list-inline-item hide-phone app-search">
                <form role="search" class="">
                    <input type="text" placeholder="Search..." class="form-control">
                    <a href=""><i class="fa fa-search"></i></a>
                </form>
            </li>

            <li class="list-inline-item dropdown notification-list">
                <a class="nav-link dropdown-toggle arrow-none waves-effect nav-user" data-toggle="dropdown" href="#" role="button"
                   aria-haspopup="false" aria-expanded="false">
                    <img src="{{ asset('assets/admin/images/admin.avif') }}" alt="user" class="rounded-circle">
                </a>
                <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                    <!-- item-->
                    <div class="dropdown-item noti-title">
                        <h5>Welcome, {{ auth('admin')->user()->name ?? auth('admin')->user()->username ?? session('admin_name', 'Admin') }}</h5>
                    </div>
                    <a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="mdi mdi-account-circle m-r-5 text-muted"></i> Profile</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('admin.logout') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left; padding: 0.5rem 1.5rem; color: #6c757d;">
                            <i class="mdi mdi-logout m-r-5 text-muted"></i> Logout
                        </button>
                    </form>
                </div>
            </li>

        </ul>

        <ul class="list-inline menu-left mb-0">
            <li class="float-left">
                <button class="button-menu-mobile open-left waves-light waves-effect">
                    <i class="mdi mdi-menu"></i>
                </button>
            </li>
        </ul>

        <div class="clearfix"></div>

    </nav>

</div>
<!-- Top Bar End -->

