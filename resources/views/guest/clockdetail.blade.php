<!DOCTYPE html>
<html lang="en">

  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Trashscan </title>
     <link rel="icon" href="{{url('assets/images/TrashScan.ico')}}" type="image/x-icon" />
    <!-- Bootstrap -->
    <link href="{{url('assets/vendors/bootstrap/dist/css/bootstrap.min.css')}}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{url('assets/vendors/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    
    <!-- Custom Theme Style -->
   <link href="{{url('assets/build/css/custom.min.css')}}" rel="stylesheet">

   <link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
   <style>
   body {
    background: #FFFFFF;
  }
  .img-clas{
    max-width: 1024px;
    max-height: 768px;
    margin: auto;
    width: 100%;
  }
  .img-waper{
    height: 90vh;
    overflow: hidden;
  }
   </style>
  </head>

  <body class="nav-md">
    <div class="body">
      <div class="main_container">
       <div class="right_col" role="main" style="margin:0px !important;background:white;">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
              
       @if(!is_null($reporting) && $reporting->is_admin == 1)       
           
                         <div class="x_title">
                        <h2 style="font-size: initial;">
                          <i class="fa fa-user"></i> 
                          {{$reporting->name or ""}}
                        </h2>
                        <div class="clearfix"></div>
                      </div> 
                        <div class="x_content">
                          <div class="accordion" id="accordion" role="tablist" aria-multiselectable="true">
                            @foreach($users as $user)
                            <div class="panel">
                              <a class="panel-heading" role="tab" id="heading{{$loop->iteration}}" data-toggle="collapse" data-parent="#accordion" href="#collapse{{$loop->iteration}}" aria-expanded="true" aria-controls="collapse{{$loop->iteration}}">
                                <h4 class="panel-title">#{{$user->id}} {{ucwords($user->name)}}</h4>
                              </a>
                              <div id="collapse{{$loop->iteration}}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading{{$loop->iteration}}">
                                <div class="panel-body">
                                  <table class="table table-bordered">
                                    <thead>
                                      <tr>
                                        <th>S.No</th>
                                        <th>Name</th>
                                        <th>Clock-In Time</th>
                                        <th>Clock-Out Time</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      @forelse($user->getManagerUsers as $managerUsers)
                                        <tr>
                                          <td scope="row">{{$loop->iteration}}</td>
                                          <td>{{ucwords($managerUsers->name)}}</td>
                                          <td>
                                            @foreach($managerUsers->clockDetail as $clockIn)
                                                @if(!empty($clockIn->clock_in))
                                                  {{\Carbon\Carbon::parse($clockIn->clock_in)->timezone($reporting->timezone)->format("m-d-Y h:i A")}}
                                                @else
                                                  -  
                                                @endif
                                                @php break; @endphp
                                            @endforeach
                                          </td>
                                          <td>
                                            @foreach($managerUsers->clockDetail as $clockOut)
                                                @if(!empty($clockOut->clock_out))
                                                  {{\Carbon\Carbon::parse($clockOut->clock_out)->timezone($reporting->timezone)->format("m-d-Y h:i A")}}
                                                @else
                                                  -  
                                                @endif
                                                @php break; @endphp
                                            @endforeach
                                          </td>
                                        </tr>  
                                      @empty
                                        <tr>
                                          <td colspan="5" align="center">No record found.</td>
                                        </tr>    
                                      @endforelse
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                            </div>
                            @endforeach
                          </div>
                        </div>

       @elseif(!is_null($reporting))
       
        <!-- Only for admin role: Start --> 
        <div class="x_title">
            <h2 style="font-size: initial;">
              <i class="fa fa-user"></i> 
                {{ucwords($reporting->name)}} 
            </h2>
            <div class="clearfix"></div>
        </div> 
        <div class="x_content">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Clock-In</th>
                <th>Clock-Out</th>
              </tr>
            </thead>
            <tbody>
                @forelse($users as $user) 
                  <tr>
                    <th scope="row">{{$loop->iteration}}</th>
                    <td>{{ucwords($user->name)}}</td>
                    <td>
                      @foreach($user->clockDetail as $clockDetail)
                        @if(!empty($clockDetail->clock_in))
                          {{\Carbon\Carbon::parse($clockDetail->clock_in)->timezone($reporting->timezone)->format("m-d-Y h:i A")}}
                      @else
                        -  
                      @endif
                        @php break; @endphp
                      @endforeach
                      </td>
                        <td>
                          @foreach($user->clockDetail as $clockDetail)
                            @if(!empty($clockDetail->clock_out))
                              {{\Carbon\Carbon::parse($clockDetail->clock_out)->timezone($reporting->timezone)->format("m-d-Y h:i A")}}
                          @else
                            -  
                          @endif
                            @php break; @endphp
                          @endforeach
                      </td>                                        
                       </tr>
                    @empty   
                      <tr>
                        <td colspan="5" align="center">No record found.</td>
                      </tr> 
                    @endforelse
                            </tbody>
                      </table>
                  </div> 
        <!-- Only for admin role: End --> 

        @else
        <div class="x_content img-waper"> 
            <img src="{{url('assets/images/session-expired.jpg')}}" style="width: 100%" class="img-responsive img-clas">
        </div>           
        @endif  
      </div>
    </div>

    <script src="{{url('assets/vendors/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{url('assets/vendors/bootstrap/dist/js/bootstrap.min.js')}}"></script>
    <script src="{{url('assets/build/js/custom.min.js')}}"></script>
    <script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
  </body>
</html>