@extends('layouts.user_app')
@extends('layouts.menu')
@section('css')
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="{{url('assets/css/responsive.dataTables.min.css')}}" rel="stylesheet">
<style>
    table.jambo_table thead:first-child > tr:first-child > th{ background: rgba(52,73,94,1); }
    .btn-primary {
        color: #fff;
        background-color: #3E5566;
        border-color: #3E5566;
    }
    .btn-primary:hover {
        color: #fff;
        background-color: #3E5566;
        border-color: #3E5566;
    }   
    span.tag {
        background: #3E5566;
    }
    .modal-dialog {
        min-height: 40%; 
    }
    .modal-content {
        min-height: 100%; 
    }
    .buttonload {
        background-color: #4CAF50; 
        border: none; 
        color: white; 
        padding: 12px 16px; 
        font-size: 16px 
    }
 .dataTables_processing 
 {
    height: 50px !important; 
 }
 .pagination>.active>a,
 .pagination>.active>a:focus,
 .pagination>.active>a:hover,
 .pagination>.active>span,
 .pagination>.active>span:focus,
 .pagination>.active>span:hover
 {
    color: white !important;
 }
 table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before, 
 table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
    background-color: #3F5368;
 }

 .select2-container--default 
 .select2-selection--multiple, 
 .select2-container--default 
 .select2-selection--single {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 3px;
    min-height: 30px;
}
.select2-container--default 
.select2-selection--single 
.select2-selection__arrow {
    height: 30px;
}
.select2-container--default 
.select2-selection--single 
.select2-selection__rendered {
    color: #444;
    line-height: 21px;
}
span#select2-name-container {
    color: #555;
    font-size: 12px;
}

.dataTables_wrapper>.row {
    overflow: unset!important;
}
td {
    sword-break: break-all !important;
}
.dataTables_wrapper .dt-buttons {
        display: none;
    }
</style>
@endsection
@section('content')
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-md-6">
                    <h2>Subscribers List</h2>
                </div>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a class="btn btn-primary" href="{{url('admin/addsubscriber')}}">
                            + Add Subscriber
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div> 
            <div class="x_content">
                <div class="table-responsive">
                    <table id="subscriber" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings"> 
                                <th class="column-title" width="5%">S.No</th>
                                <th class="column-title" width="10%">Company</th>
                                <th class="column-title" width="3%">Email</th>
                                <th class="column-title" width="10%">Mobile</th>
                                <th class="column-title" width="10%">Last Login</th>
                                <th class="column-title" width="10%">Create Date</th>
                                <th class="column-title" width="10%">Start Date</th> 
                                <th class="column-title" width="10%">End Date</th>
                                <th class="column-title" width="10%">Status</th>
                                <th class="column-title" width="10%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--Model for add template: Start-->
<div id="add-superadmin" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Reset Password</h4>
            </div>
            <form class="form-horizontal form-label-left" 
                  action="{{url('admin/resetSubscriberPassword')}}"
                  method="post" id="superadmin-template">
                
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <input type="password" 
                                   id="password" 
                                   name="password" required 
                                   class="form-control validate-space" 
                                   placeholder="Password">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <input type="password" 
                                   id="confirmPass" 
                                   name="confirmPass" required 
                                   class="form-control validate-space" 
                                   placeholder="Confirm Password">
                        </div>
                    </div>
                    
                    <input type="hidden" 
                           name="subscriber_id" id="subscriber_id" required value=""
                           class="form-control" />
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" id="sub-btn" class="btn btn-primary"> Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--Model for add template: End-->
@endsection 
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/subscriber.list.js')}}"></script>
<script>
    $('.makeaction').click(
        function () {
            $('#preloader').show();
        }
    );
</script>
@endsection

