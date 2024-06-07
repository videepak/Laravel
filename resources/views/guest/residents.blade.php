<!DOCTYPE html>
<html lang="en">

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf_token" content="{{csrf_token()}}" />

    <title>Trashscan </title>
    <link rel="icon" href="{{url('assets/images/TrashScan.ico')}}" type="image/x-icon" />
    <!-- Bootstrap -->
    <link href="{{url('assets/vendors/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{url('assets/vendors/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    
    <!-- Custom Theme Style -->
    <link href="{{url('assets/build/css/custom.min.css')}}" rel="stylesheet">
   
    <link href="{{url('assets/vendors/iCheck/skins/flat/green.css')}}" rel="stylesheet">
    <style>
    .nav-md .container.body .right_col {
      margin-left: 0px;
    }
  </style>
  </head>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Residents <small></small></h2>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">
                    <br />
                    @if(session('success'))
                      <div class="alert alert-success" role="alert">
                        {{session('success')}}
                      </div>
                    @endif
                    <form id="demo-form2" action="{{ url('/guest/update-residents/') }}" class="form-horizontal form-label-left" method="POST">
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12"></label>
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">{{ $property->name }}</label>
                      </div>
                      {!! Form::token() !!}
                      <input type="hidden" name='propertyId' id='propertyId' value="" readonly>
                      <div class="form-group">
                        <label 
                          class="control-label col-md-3 col-sm-3 col-xs-12">Building / Unit <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <select class="select2_group form-control" name="unitId" id="get-unit" required>
                              <option value="">Select Unit</option>
                              @foreach($property->getBuildingIsActiveUnit as $build)
                                <optgroup label="{{ $build->building_name }}">
                                  @foreach($build->getUnit as $unit)
                                    <option value="{{ $unit->id }}">
                                        {{ $unit->unit_number }}
                                    </option>
                                  @endforeach
                                </optgroup>
                              @endforeach
                          </select>
                        </div>
                        @if ($errors->has('unitId'))
                          <span style="color: red" role="alert">
                            <strong>{{ $errors->first('unitId') }}</strong>
                          </span>
                        @endif
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">First Name <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" name='firstname' id="first-name"
                            required="required"
                            value="{{ old('firstname') }}" 
                            class="form-control col-md-7 col-xs-12">
                        </div>
                        @if ($errors->has('firstname'))
                          <span style="color: red" role="alert">
                            <strong>{{ $errors->first('firstname') }}</strong>
                          </span>
                        @endif
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Last Name <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="last-name" name="lastname" 
                            required="required"
                            value="{{ old('lastname') }}""
                            class="form-control col-md-7 col-xs-12">
                        </div>
                        @if ($errors->has('lastname'))
                          <span style="color: red" role="alert">
                            <strong>{{ $errors->first('lastname') }}</strong>
                          </span>
                        @endif
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="email" id="email" name="email"
                              required="required"
                              value="{{ old('email') }}""
                              class="form-control col-md-7 col-xs-12">
                        </div>
                        @if ($errors->has('email'))
                          <span style="color: red" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                          </span>
                        @endif
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Mobile <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="number" id="mobile" name="mobile"
                              required="required"
                              value="{{ old('mobile') }}""
                              class="form-control col-md-7 col-xs-12">
                        </div>
                        @if ($errors->has('mobile'))
                          <span style="color: red" role="alert">
                            <strong>{{ $errors->first('mobile') }}</strong>
                          </span>
                        @endif
                      </div>
                      <div class="form-group" id="checkinForm" style="display:none">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Last Check-in 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" id="checkIn" readonly class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="form-group" id="reminderfrom" style="display:none">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Rules For Service 
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <textarea type="text" id="reminder" readonly class="form-control col-md-7 col-xs-12" rows="5"></textarea>
                        </div>
                      </div>
                      
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">
                          <input type="checkbox" class="flat" name="iagree" required> 
                        </label>
                        <div class="checkbox col-md-6 col-sm-6 col-xs-12">
                            <label>
                              <b>I Agree.</b>
                            </label>
                        </div>
                        @if ($errors->has('iagree'))
                        <span style="color: red" role="alert">
                          <strong>{{ $errors->first('iagree') }}</strong>
                        </span>
                      @endif
                      </div>

                     
                      
                      
                      
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                          <button type="button" class="btn btn-success subform">Submit</button>
                        </div>
                      </div>

                    </form>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
        <!-- /page content -->

        
      </div>
    </div>	
  </body>
  <script src="{{url('assets/vendors/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{url('assets/vendors/bootstrap/dist/js/bootstrap.min.js')}}"></script>
  <script src="{{url('assets/build/js/custom.min.js')}}"></script>
  <script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
  <script src="{{url('assets/vendors/iCheck/icheck.min.js')}}"></script> 
  
  <script>var BaseUrl = "{{url('')}}";</script>
<script>
$(document).ready(function () {
  $(".subform").click(function(){
    if (confirm("Are want to receive service notifications ?")) {
      $( "#demo-form2" ).submit();
    }
  });

  $(document).on("change", "#get-unit", function () {	
    let id = $(this).val();
    
    $.ajax({
      url: BaseUrl + '/guest/get-residents',
      type: "POST",
      data: {
          _token: $('meta[name="csrf_token"]').attr('content'),
          id: id,
      },
      success: function (data) {
        console.log(data);
        $("#propertyId").val(data.property.id);
       
        $("#reminder").val(data.property.reminder);
        $("#checkIn").val(`${data.property.check_in_property.create_at} (UTC)`);
        $('#checkinForm').css('display', 'block');
        $('#reminderfrom').css('display', 'block');
      }
    });
  });
});
</script>
</html>