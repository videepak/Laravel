@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<style>  
.parsley-required {
  display:block; 
  padding-left: 15px; 
} 
#example_processing {
        height: 50px !important;
    }
    .pagination>.active>a,
    .pagination>.active>a:focus,
    .pagination>.active>a:hover,
    .pagination>.active>span,
    .pagination>.active>span:focus,
    .pagination>.active>span:hover {
        color: white !important;
    }
    .set-width {
        width: 205%;
        margin-left: -89%;
    }
    .select2-container--default
    .select2-selection--multiple
    .select2-selection__choice {
        background-color: #3E5566;
        color: white;
    }
    .badge {
        padding: 2px 5px;
        border: 0px solid #1ABB9C !important;
    }
    .dataTables_wrapper .dt-buttons {
        display: none;
    }
</style>
@endsection
@section('content')
<div class="right_col" role="main">
		<?php
        $auth = Auth::user();
        $user_property = DB::table('properties')->where('user_id', $auth->id)->count();
        if ($auth->trial == 'yes') {
            if (date('Y-m-d') > $auth->trial_end) { //free expired
                $current = DB::table('subscribers')->where('user_id', $auth->id)->first();

                $p = DB::table('subscriptions')->where('id', $current->subscription_id)->first();
                $property_limit = $p->number_of_property;
            } else {
                $p = DB::table('subscriptions')->where('id', '28')->first();
                $property_limit = $p->number_of_property;
            }
        }
        ?>
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Property List </h2>
                @if(!$user->hasRole(['property_manager']))
                <ul class="nav navbar-right panel_toolbox">
				<?php if (isset($property_limit) && $user_property == $property_limit) {
                } else {?>
                    <li>
                        <a class="btn btn-primary pull-right" 
                           href="{{url('property/create')}}">
                            + Add Property
                        </a>
                    </li>
				<?php } ?>
                <li>
                    <a class="btn btn-primary pull-right excel-option" >
                    <i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel </a>
                </li>
                </ul>					
                @endif
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table id="example" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings"> 
                                <th class="column-title">S.no </th>
                                <th class="column-title">Name </th>
                                <th class="column-title">Type </th>
                                <!-- <th class="column-title">Units </th> -->
                                <th class="column-title">Address </th>
                                <!-- <th class="column-title">City</th>
                                <th class="column-title">State</th>
                                <th class="column-title">Zip</th> -->
                                <th class="column-title">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>       
<!-- Modals Start -->
<div id="assign_user_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Assign Property</h4>
            </div>

            <form action="" id="add_emp_property" name="add_emp_property" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" method="post">
                <div class="modal-body">

                    <div id="error_message" class="error"></div>
                    <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                    <label>Select Employee</label> 
                    <select class="select_emp" name="employees[]" id="emp" multiple="multiple" required="required" data-parsley-required-message="Please select employee.">
                        <option value=""></option>
                        @foreach($employee as $empdetails)
                        @if($empdetails->firstname != "")
                        <option value="{{$empdetails->id}}"  id="empid">{{ucwords($empdetails->firstname)}} {{ucwords($empdetails->lastname)}}</option>
                        @endif
                        @endforeach
                    </select>

                    <input type="hidden" id="property_id" name="property_id" value="">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary submit-btn-user add_user_pro">Add</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="printqrcodeProperty" class="modal fade" role="dialog">
    <div class="modal-dialog modal-sm">
        <!-- Modal content-->
        <div class="modal-content">

        </div>
        
    </div>
</div>
<!-- Modals End -->
<form id="deleteProperty" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>
@endsection 
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/property.list.js')}}"></script>

<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
<script>

    function deleteProperty(element, event)
    {
        event.preventDefault();
        if (confirm('Are you sure you want to continue?'))
        {
            var url = $(element).attr('href');
            $('#deleteProperty').attr('action', url);
            $('#deleteProperty').submit();
        }
    }
    // function printDivProperty(elem)
    // {
    //     var divToPrint = document.getElementById(elem);
    //     newWin = window.open("");
    //     newWin.document.write(divToPrint.outerHTML);
    //     newWin.print();
    //     newWin.close();
    // }
     

    
</script>
@endsection





