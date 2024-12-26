<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{route('admin.index')}}">
        <div class="sidebar-brand-icon rotate-n-15">
            <img src="{{asset('img/ic_launcher.png')}}" alt="icon" style="width: 35px; height:35px;">
        </div>
        <div class="sidebar-brand-text mx-3">Ba Ba Gyi <sup>Admin</sup></div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{$page_name == "Dashboard"? 'active' : ''}}">
        <a class="nav-link" href="{{route('admin.index')}}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Financial
    </div>

    <!-- Nav Item - Utilities Collapse Menu -->
    <li class="nav-item {{$page_name == "Financial"? 'active' : ''}}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFinancial"
            aria-expanded="true" aria-controls="collapseFinancial">
            <i class="fas fa-fw fa-dollar-sign"></i>
            <span>Financial</span>
        </a>
        <div id="collapseFinancial" class="collapse" aria-labelledby="headingUtilities"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Financial:</h6>
                <a class="collapse-item" href="{{route('admin.transactions')}}">Top Up</a>
                <a class="collapse-item" href="{{route('admin.withdraws')}}">Withdraw</a>
            </div>
        </div>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Lottery
    </div>

    <!-- Nav Item - Utilities Collapse Menu -->
    <li class="nav-item {{$page_name == "Vouchers"? 'active' : ''}}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseVouchers"
            aria-expanded="true" aria-controls="collapseVouchers">
            <i class="fas fa-fw fa-money-bill-alt"></i>
            <span>Vouchers</span>
        </a>
        <div id="collapseVouchers" class="collapse" aria-labelledby="headingUtilities"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Vouchers</h6>
                {{-- <a class="collapse-item" href="{{route('admin.vouchers.btc-2d')}}">BTC 2D</a> --}}
                <a class="collapse-item" href="{{route('admin.vouchers.thai-2d')}}">MM 2D</a>
                <a class="collapse-item" href="{{route('admin.vouchers.thai-3d')}}">MM 3D</a>
            </div>
        </div>
    </li>

    <li class="nav-item {{$page_name == "Reports"? 'active' : ''}}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReport"
            aria-expanded="true" aria-controls="collapseReport">
            <i class="fas fa-fw fa-download "></i>
            <span>Reports</span>
        </a>
        <div id="collapseReport" class="collapse" aria-labelledby="headingUtilities"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Reports</h6>
                {{-- <a class="collapse-item" href="{{route('admin.vouchers.btc-2d')}}">BTC 2D</a> --}}
                <a class="collapse-item" href="{{route('admin.reports')}}?lottery_type_id=2&clock_id=2">MM 2D 12:01 PM</a>
                <a class="collapse-item" href="{{route('admin.reports')}}?lottery_type_id=2&clock_id=4">MM 2D 04:30 PM</a>
                <a class="collapse-item" href="{{route('admin.reports')}}?lottery_type_id=3&clock_id=5">MM 3D</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Utilities Collapse Menu -->
    <li class="nav-item {{$page_name == "Number Setting"? 'active' : ''}}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseNumberSetting"
            aria-expanded="true" aria-controls="collapseNumberSetting">
            <i class="fas fa-fw fa-cog"></i>
            <span>Number Setting</span>
        </a>
        <div id="collapseNumberSetting" class="collapse" aria-labelledby="headingUtilities"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Number Setting</h6>
                {{-- <a class="collapse-item" href="{{route('admin.vouchers.btc-2d')}}">BTC 2D</a> --}}
                <a class="collapse-item" href="{{route('admin.numbers')}}?lottery_type_id=2&clock_id=2">MM 2D 12:01 PM</a>
                <a class="collapse-item" href="{{route('admin.numbers')}}?lottery_type_id=2&clock_id=4">MM 2D 04:30 PM</a>
                <a class="collapse-item" href="{{route('admin.numbers')}}?lottery_type_id=3&clock_id=5">MM 3D</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Utilities Collapse Menu -->
    <li class="nav-item {{$page_name == "Calendar"? 'active' : ''}}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCalendar"
            aria-expanded="true" aria-controls="collapseCalendar">
            <i class="fas fa-fw fa-calendar-alt"></i>
            <span>Calendar</span>
        </a>
        <div id="collapseCalendar" class="collapse" aria-labelledby="headingUtilities"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Calendar:</h6>
                {{-- <a class="collapse-item" href="{{route('admin.vouchers.btc-2d')}}">BTC 2D</a> --}}
                <a class="collapse-item" href="{{route('admin.lotteries.records')}}">MM 2D</a>
                <a class="collapse-item" href="{{route('admin.lotteries.records')}}?type=3">MM 3D</a>
            </div>
        </div>
    </li>

    <li class="nav-item {{$page_name == "Lottery Setting"? 'active' : ''}}">
        <a class="nav-link" href="{{route('admin.lottery-types')}}">
            <i class="fas fa-fw fa-chess-king"></i>
            <span>Lottery Setting</span></a>
    </li>

    <hr class="sidebar-divider">

    <li class="nav-item {{$page_name == "Users"? 'active' : ''}}">
        <a class="nav-link" href="{{route('admin.users')}}">
            <i class="fas fa-user fa-fw"></i>
            <span>User</span>
        </a>
    </li>

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item {{$page_name == "Setting"? 'active' : ''}}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-fw fa-cog"></i>
            <span>Setting</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Admin Setting:</h6>
                <a class="collapse-item" href="{{route('admin.admins')}}">Admin List</a>
                <a class="collapse-item" href="{{route('admin.payment-methods')}}">Payment Methods</a>
                <a class="collapse-item" href="{{route('admins.mobile-versions')}}">Mobile Versions</a>
                <a class="collapse-item" href="{{route('admin.contacts')}}">Misc Setting</a>
                <a class="collapse-item" href="{{route('admins.holidays')}}">Holiday</a>
            
            </div>
        </div>
    </li>

    

    <li class="nav-item">
        <a class="nav-link" href="{{route('logout')}}">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span></a>
    </li>
  

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

    <!-- Sidebar Message -->
    <div class="sidebar-card d-none d-lg-flex">
        <img class="sidebar-card-illustration mb-2" src="{{asset('img/undraw_rocket.svg')}}" alt="...">
        <p class="text-center mb-2"><strong>BaBaGyi Admin</strong> is packed with the best feactures</p>
    </div>

</ul>