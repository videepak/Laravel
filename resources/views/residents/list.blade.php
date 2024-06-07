@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<!-- Datatables -->
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/1.6.3/css/buttons.dataTables.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
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
    .dataTables_wrapper .dt-buttons {
        display: none;
    }
    .upload-btn-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
    }
  
  .btn {
    border: 1px solid #169F85;
    color: #fff;
    background-color: #26B99A;
    padding: 5px 5px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: inherit;
    }
  
  .upload-btn-wrapper input[type=file] {
    font-size: 100px;
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    }
    .sendResident{
        color: red;
    }
    span.tag {
        background: #3E5566;
    }
    .service_alert{
        font-size: 15px;
        margin-left: 25px;
    }
</style>
@endsection
@section('content')       
@yield('menu')     
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Manage Resident</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li id="show-enties"></li>
                    <li id="newSearchPlace"></li>
                    <li>
                        <a class="btn btn-primary pull-right" 
                           href="{{url('property-manager/resident/create')}}">
                            + Add Resident 
                        </a>
                    </li>
                    <li>
                     <a class="btn btn-primary pull-right" 
                        href="{{url('property-manager/download-resident')}}">
                           Download Sample
                     </a>
                  </li>
                  <li class="upload-btn-wrapper">
                    {!! Form::open(array('route' => 'residents.import','method'=>'POST','files'=>'true','id'=>'uploadResident')) !!}
                        <button class="btn btn-success">Mass Upload</button>
                        <input type="file" id="residentSubmit" name="residents" />
                    {!! Form::close() !!}
                  </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="table-responsive">
                    <table id="example" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">
                                <th class="column-title">S.No</th>
                                <th class="column-title">Name</th>
                                <th class="column-title">Mobile</th>
                                <th class="column-title">Email</th>
                                <th class="column-title">Unit Name</th>
                                <th class="column-title"># of Violation</th>
                                <th class="column-title">Service Alert</th>
                                <th class="column-title">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>   
<form id="deleteResident" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>
<!--Model For Mail Send: Start-->
<div id="resident-mail-popup" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Send Email</h4>
            </div>
            <div class="modal-body">
                <form id="demo-form2 resident-mail-popup" method="post" class="form-horizontal form-label-left">
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="template"> Template: 
                            <span class="required" style='color:red'>*</span>
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <select class="form-control col-md-7 col-xs-12" id="template-data" required="required" name="template">
                                <option>Select</option> 
                                    @if(isset($residentTemplate))
                                        @foreach($residentTemplate as $residentValue)
                                            <option value="{{$residentValue->id}}">{{$residentValue->name}}</option>
                                        @endforeach
                                    @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="To"> To: 
                            <span class="required" style='color:red'>*</span>
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input id="residenttagsemail" type="email" name="toresidentemail" class="form-control residenttagsemail">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="Cc" class="control-label col-md-3 col-sm-3 col-xs-12"> Cc:
                            <span class="required"style='color:red'>&nbsp;</span></label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input id="cc-mail" type="email" required="required" class="form-control residenttagsemail">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="subject"> Subject: 
                        </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <input type="text" 
                                id="to-subject" 
                                value=""
                                class="form-control col-md-7 col-xs-12" 
                                name="subject"
                                required="required">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"  for="body">Body: </label>
                        <div class="col-md-9 col-sm-9 col-xs-12">
                            <textarea id="to-body" name="body" class="form-control col-md-7 col-xs-12" rows="10" cols="50" required="required"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="hidden" id='resident-email'>
                            <input type="hidden" id='template_name'>
                        </div>
                    </div>

                    <div class="modal-footer">
                        
                        <button id="send-mail-resident" type="button" class="btn btn-primary"> Send</button>
                        <button id="send-mail-close" type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--Model For Mail Send: End-->



@endsection 
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/resident.list.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js" integrity="sha512-rstIgDs0xPgmG6RX1Aba4KV5cWJbAMcvRCVmglpam9SoHZiUCyQVDdH2LPlxoHtrv17XWblE/V/PP+Tr04hbtA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  function deleteResident(element, event)
  {
      event.preventDefault();
      if (confirm('Are you sure you want to continue?'))
      {
          var url = $(element).attr('href');
          $('#deleteResident').attr('action', url);
          $('#deleteResident').submit();
      }
  }
 
</script>
@endsection 

