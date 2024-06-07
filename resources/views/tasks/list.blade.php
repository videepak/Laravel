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
    .select2-container {
        display: block;
    }
    span.select2.select2-container.select2-container--default {
        width: 100% !important;
    }
</style>
@endsection
@section('content')
<div class="right_col" role="main">
	<div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Tasks List </h2>
                <ul class="nav navbar-right panel_toolbox">
	                <li>
                        <a class="btn btn-primary pull-right" href="javascript:void(0);" 
                            data-toggle="modal" data-target="#add-template">
                            + Add Task
                        </a>
                    </li>
	            </ul>					
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table id="task" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings"> 
                                <th class="column-title">S.no</th>
                                <th class="column-title">Name</th>
                                <th class="column-title">Date</th>
                                <th class="column-title">Task Owner</th>
                                <th class="column-title">Photo Required</th>
                                <th class="column-title">Notify Property Manager</th>
                                <th class="column-title">Property </th>
                                {{-- <th class="column-title">Address </th> --}}
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
<!--Model for add template: Start-->
<div id="add-template" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Task</h4>
            </div>
            <form class="form-horizontal form-label-left" 
                  action="{{url('tasks')}}"
                  id='task-form'
                  method="post" id="">
                
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <label>Task Title *</label>
                            <input type="text" 
                                   id="name" 
                                   name="taskTitle" required 
                                   class="form-control validate-space" 
                                   placeholder="">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <label>Description *</label>
                            <textarea 
                                name="description"
                                id="description" 
                                class="form-control col-md-7 col-xs-12 validate-space" 
                                rows="5" 
                                placeholder=""
                                cols="40"
                                required></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <label>Start & End Date *</label>
                                <input type="text"
                                   id="start"
                                   name="datefilter" 
                                   class="form-control validate-space datepicker" 
                                   required
                                />
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <label>Property *</label>
                            <select class="form-control"
                                id="task-select" 
                                name="property_id">
                                <option value="">Select Property</option>
                                    @if(isset($properties) && ($properties->isNotEmpty()))
                                        @foreach($properties as $pro)
                                            <option value="{{$pro->id}}">{{ucwords($pro->name)}}</option>
                                        @endforeach
                                    @endif
                            </select>
                        </div>
                    </div>

                    
                    
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12 is-photo">
                            <label>Photo Required *</label>
                                <input type="checkbox" name="onoff" id="is-photo" class="js-switch onoff" />
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12 notify">
                            <label>Notify Property Manager</label>
                                <input type="checkbox" name="notify" id="notify" class="js-switch onoff" />
                        </div>
                    </div>
            
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <label>Frequency *</label>
                            <div class="radio">
                                <label>
                                   <input type="radio" class="flat frequency day" name="frequency" value="1" checked> Daliy
                                </label>
                                <label>
                                   <input type="radio" class="flat day" name="frequency" value="2"> Weekly
                                </label>
                                <label>
                                   <input type="radio" class="flat day" name="frequency" value="3"> Monthly
                                </label>
                             </div>
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
<form id="deleteTask" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>
@endsection 
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/task.list.js')}}"></script>

<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
<script>
    $(function() {
        $('input[name="datefilter"]').daterangepicker({
                minDate:new Date(),
                autoUpdateInput: false,
                locale: {
                cancelLabel: 'Clear'            
            }
        });

        $('input[name="datefilter"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('input[name="datefilter"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });

    function deleteProperty(element, event)
    {
        event.preventDefault();
        if (confirm('Are you sure you want to continue?'))
        {
            var url = $(element).attr('href');
            $('#deleteTask').attr('action', url);
            $('#deleteTask').submit();
        }
    }
    
</script>
@endsection





