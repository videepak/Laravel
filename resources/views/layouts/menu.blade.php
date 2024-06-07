@section('menu')
<div class="col-md-3 left_col">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <a href="#" class="site_title" style="font-size: 22px;line-height: 68px !important;">
                <span style="display:block !important;">
                    <img src="{{url('/assets/production/images')}}/trashscanlogo.png" 
                         title="@if(isset($subscriber_details) && !empty($subscriber_details->company_name)){{ ucwords($subscriber_details->company_name) }} @else Trash Scan @endif"/>
                    Trash Scan
                </span>
            </a>
        </div>
        <div class="clearfix"></div>
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <ul class="nav side-menu">
                    <li>
                        <a href="javascript:void(0);">
                            <i class="fa fa-user"></i> Manage Super Admin <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="{{url('admin/super-admin/')}}">Manage Super Admin </a>
                            </li> 
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);">
                            <i class="fa fa-user"></i> Manage Subscribers <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <li><a href="{{url('admin/subscribers')}}">Manage Subscribers</a></li> 
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);">
                            <i class="fa fa-user">
                                </i> Manage Users <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="{{url('admin/users')}}">Manage Users</a>
                            </li> 
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);">
                            <i class="fa fa-user"></i> Manage Subscription <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="{{url('admin/subscriptions')}}">Manage Subscription</a>
                            </li> 
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);">
                            <i class="fa fa-user">
                                </i> Manage Tickets <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="{{url('admin/tickets')}}">Manage Tickets</a>
                            </li> 
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);">
                            <i class="fa fa-user">
                                </i> Manage Report <span class="fa fa-chevron-down"></span>
                        </a>
                        <ul class="nav child_menu">
                            <li>
                                <a href="{{url('admin/reports')}}">Manage Report</a>
                            </li>
                            <li>
                                <a href="{{url('admin/reports-logs')}}">Report Logs</a>
                            </li> 
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
       
    </div>
</div>
<div class="top_nav">
    <div class="nav_menu">
        <nav>
            <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li class="">
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name }}
                        <span class=" fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu pull-right">                  
                        <li>
                            <a href="{{ route('logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fa fa-sign-out pull-right"></i> Log Out</a>
                        </li>
                    </ul>
                </li>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </ul>
        </nav>
    </div>
</div>
@endsection