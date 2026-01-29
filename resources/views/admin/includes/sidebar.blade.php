<!-- ========== Left Sidebar Start ========== -->
<div class="left side-menu">
    <button type="button" class="button-menu-mobile button-menu-mobile-topbar open-left waves-effect">
        <i class="ion-close"></i>
    </button>

    <!-- LOGO -->
    <div class="topbar-left">
        <div class="text-center">
            <a href="{{ route('admin.dashboard') }}" class="logo" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                <img src="{{ asset('assets/admin/images/logo.png') }}" alt="Crutox" class="" style="max-height: 40px; margin-top:-5px; width: auto;">
                <span style="color: #fff; font-size: 22px; font-weight: bold;">Crutox</span>
            </a>
        </div>
    </div>

    <div class="sidebar-inner niceScrollleft">

        <div id="sidebar-menu">
            <ul>
                <li class="menu-title">Main</li>

                <li>
                    <a href="{{ route('admin.dashboard') }}" class="waves-effect {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="mdi mdi-airplay"></i>
                        <span> Dashboard </span>
                    </a>
                </li>

                <li class="menu-title">Content Management</li>

                <li>
                    <a href="{{ route('admin.news.index') }}" class="waves-effect {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                        <i class="mdi mdi-newspaper"></i>
                        <span> News Management </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.tasks.index') }}" class="waves-effect {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                        <i class="mdi mdi-checkbox-multiple-marked"></i>
                        <span> Tasks Management </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.shop.index') }}" class="waves-effect {{ request()->routeIs('admin.shop.*') ? 'active' : '' }}">
                        <i class="mdi mdi-store"></i>
                        <span> Shop Management </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.giveaway.index') }}" class="waves-effect {{ request()->routeIs('admin.giveaway.*') ? 'active' : '' }}">
                        <i class="mdi mdi-gift"></i>
                        <span>Giveaway </span>
                    </a>
                </li>

                <li class="menu-title">Settings</li>

                <li>
                    <a href="{{ route('admin.app-settings') }}" class="waves-effect {{ request()->routeIs('admin.app-settings') ? 'active' : '' }}">
                        <i class="mdi mdi-tune"></i>
                        <span> App Settings </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.mining-settings') }}" class="waves-effect {{ request()->routeIs('admin.mining-settings') ? 'active' : '' }}">
                        <i class="mdi mdi-speedometer"></i>
                        <span> Mining Speed Settings </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.referral-settings') }}" class="waves-effect {{ request()->routeIs('admin.referral-settings') ? 'active' : '' }}">
                        <i class="mdi mdi-account-multiple-plus"></i>
                        <span> Referral Rewards </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.mystery-box') }}" class="waves-effect {{ request()->routeIs('admin.mystery-box') ? 'active' : '' }}">
                        <i class="mdi mdi-package-variant"></i>
                        <span> Mystery Box Settings </span>
                    </a>
                </li>

                <li class="menu-title">User Management</li>

                <li>
                    <a href="{{ route('admin.users.index') }}" class="waves-effect {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="mdi mdi-account-multiple"></i>
                        <span> Users & Coins </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.kyc.index') }}" class="waves-effect {{ request()->routeIs('admin.kyc.*') ? 'active' : '' }}">
                        <i class="mdi mdi-account-check"></i>
                        <span> KYC Management </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.badges.index') }}" class="waves-effect {{ request()->routeIs('admin.badges.*') ? 'active' : '' }}">
                        <i class="mdi mdi-medal"></i>
                        <span> Badges Management </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.kyc-settings') }}" class="waves-effect {{ request()->routeIs('admin.kyc-settings') ? 'active' : '' }}">
                        <i class="mdi mdi-tune"></i>
                        <span> KYC Settings </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.user-stats') }}" class="waves-effect {{ request()->routeIs('admin.user-stats') ? 'active' : '' }}">
                        <i class="mdi mdi-chart-line"></i>
                        <span> User Stats </span>
                    </a>
                </li>

            </ul>
        </div>
        <div class="clearfix"></div>
    </div> <!-- end sidebarinner -->
</div>
<!-- Left Sidebar End -->

