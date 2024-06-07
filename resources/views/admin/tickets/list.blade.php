@extends('layouts.user_app')
@extends('layouts.menu')
@section('css')
<link href="{{url('assets/css/bootstrap-editable.css')}}" rel="stylesheet">
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
                    <h2>Tickets</h2>
                </div>
               
                <div class="clearfix"></div>
            </div> 
            <div class="x_content">
                <div class="table-responsive">
                    <table id="subscriber" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title" width="2%">S.No</th>
                                <th class="column-title" width="2%">Ticket.No</th>
                                <th class="column-title" width="10%">User Name</th>
                                <th class="column-title" width="5%">Email</th>
                                <th class="column-title" width="5%">Mobile</th>
                                <th class="column-title" width="5%">
                                    <select class="form-control input-sm filter"
                                    style="min-width: 50%;" id="status">
                                        <option value="">Status</option>
                                        <option value="0">Not Started</option>
                                        <option value="1">In Progress</option>
                                        <option value="2">Closed</option>
                                        <option value="3">Archived</option>
                                    </select>
                                </th>
                                <!-- <th class="column-title" width="1%">Message</th> -->
                                <th class="column-title" width="10%">Created At</th>
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
<div id="comment-model" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Comment</h4>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12" id="comment-content"></div>
            
            <form class="form-horizontal form-label-left" 
                action="{{url('admin/add-comment')}}"
                method="post"
                id="superadmin-template">
                
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <textarea class="form-control"
                                rows="5"
                                id="comment" required
                                name="comment"
                                placeholder="Write comment here..."
                            ></textarea>

                            <input type="hidden" 
                                name="ticket_id"
                                id="ticket_id" required
                                value=""
                                class="form-control" />
                        </div>
                    </div>                     
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
<script src="{{url('assets/js/bootstrap-editable.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/ticket.list.js')}}"></script>
<script>var actionEdit = '[{"text": "Not Started", "value": 0}, {"text": "In Progress","value": 1}, {"text": "Closed","value": 2 }, { "text": "Archived", "value": 3}]';</script>
<script>
    $('.makeaction').click(
        function () {
            $('#preloader').show();
        }
    );
</script>
@endsection

