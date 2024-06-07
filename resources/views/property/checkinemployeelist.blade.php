<style>
    ul.msg_list li:last-child {
        margin-bottom: 0px !important;
        padding: 5px !important;
    }
</style>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">Employee List 
        <small style="color:#FFFFFF">({{ucwords($detail->name)}})</small>
    </h4>
</div>
<div class="modal-body" style="max-height: 500px;overflow-y: auto;">
    <ul class="list-unstyled msg_list">
        @if($detail->getCheckInUser->isNotEmpty())
        @foreach($detail->getCheckInUser as $employee)
        <li>
            <a>
                <span class="image">
                    @if(isset($employee) && !empty($employee->image_name)
                        && file_exists(public_path('/uploads/user/'.$employee->image_name)))
                    <img src="{{url('/uploads/user/'.$employee->image_name)}}">
                    @else
                    <img src="{{url('/uploads/user/default.png')}}">
                    @endif

                </span>
                <span>
                    <span> <b>{{ucwords($employee->firstname)}} {{ucwords($employee->lastname)}}</b></span>
                </span>
                @if ($employee->checkinUser->isNotEmpty())
                    <span class="time">
                        @if($employee->checkinUser)
                            Check In:
                            {{$employee->checkinUser[0]->created_at->timezone(getUserTimezone())->format('m-d-Y h:i A')}}
                        @endif
                    
                        @if($employee->checkinUser[0]->check_in_complete)
                            <br>    
                        Check Out:
                            {{$employee->checkinUser[0]->updated_at->timezone(getUserTimezone())->format('m-d-Y h:i A')}}
                        @endif
                    </span>
                @endif
                <span class="message">
                    Contact No: 
                    @if($employee->mobile)
                        {{$employee->mobile}}
                    @else
                        Not Mention.
                    @endif
                </span>
            </a>
        </li>
        @endforeach 
        @else
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="error-template">
                        <center><h2>No employee assigned to this property.</h2></center>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </ul>
</div>