@section('css')
<style>
    table.content-det tr th {
        padding: 5px;
        text-align: right;
    }
    #table tr td {
        padding: 5px;
    }
    .attachment ul li img {
        height: 192px;
        border: 1px solid #ddd;
        padding: 5px;
        margin-bottom: 10px;
    }
    .attachment ul {
        width: 100%;
        list-style: none;
        display: inline-block;
        margin-bottom: 30px;
        padding-left: 1px;
    }
    ul > li {

        display: inherit;

    }
</style>
@endsection
<div class="modal-header">
    @if(empty($violation['is_mail']))
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    @endif
    <h4 class="modal-title">
        <strong id="popup-heading">Violation Details</strong>
    </h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-sm-6 col-xs-12 col-md-7" style='padding-left: 4%;'> 
            @if(!empty($violation['status']))
            <p>                
                <b>Violation Status: </b>
                {{$violation['status']}}
            </p>
            @endif
            @if(!$user->hasRole('property_manager') && !empty($violation['user_name']))
            <p>
                <b>Employee Name: </b>
                    {{$violation['user_name']}}
            </p>
            @endif
            @if(!empty($violation['user_name']))
            <p>
                <b>Property Name: </b>
                {{$violation['property_name']}}
            </p>
            @endif
            @if(!empty($violation['address']))
            <p>
                <b>Property Address: </b>
                {{$violation['address']}}
            </p>
            @endif
            
            @if(!empty($violation['building']))
                <p><b>Buildings :</b> {{$violation['building']}}</p>
            @endif

            @if(!empty($violation['building_address']))
                <p><b>Buildings Address :</b> {{$violation['building_address']}}</p>
            @endif
           
            @if(!empty($violation['unit']))
            <p>
                @if($violation['isRoute'])
                    <b>Route Checkpoint :</b>
                @else
                    <b>Bin Tag ID: </b>
                @endif
                    
                {{$violation['unit']}}
            </p>
            @endif
            
            @if(!empty($violation['reason']))
            <p>
                <b>Reason: </b>
                {{$violation['reason']}}
            </p>
            @endif
            @if(!empty($violation['action']))
            <p>
                <b>Action: </b>
                {{$violation['action']}}
            </p>
            @endif
            @if(isset($violation['special_note']) && !empty($violation['special_note']))
            <p>
                <b>Special Note: </b>
                <a id="spacial-note" href="javascript:void(0);" data-type="textarea" data-pk="{{$violation['id']}}" data-name='special_note' data-url="{{url('violation/get-violation-for-spacial-notes')}}" data-title="Enter the notes..." >{{$violation['special_note']}}</a>
            </p>
            @endif
            @if(isset($violation['special_note']) && !empty($violation['special_note']))
            <p>
                <b>Date: </b>
                {{\Carbon\Carbon::parse($violation['created_at'])->timezone(getUserTimezone())->format('m-d-Y h:i A')}}
            </p>
            @endif
        </div>
        @role('property_manager')
        <div class="col-sm-6 col-xs-12 col-md-5" style='padding-top: 4%;'>

            <form id="demo-form">
                <label for="message">Comment :</label>
                    <textarea id="comment" rows="5" cols="5" required="required" class="form-control" placeholder="Write here..." name="message">@if(!is_null($violation['comment'])){{$violation['comment']}}@endif</textarea>
                    <br/>
                    <input type="hidden" id="violationId" value="{{$violation['id']}}">
                <span class="btn btn-primary sub-popup">Submit</span>
            </form>
        </div>
        @endrole
    </div>
    <div class="row">
        <div class="attachment" style="margin-left: 3%;">
            <ul>
                @if(count($violation['images']))
                    @foreach($violation['images'] as $img)
                        <li>
                            <a 
                                href="{{ Storage::disk('s3')->temporaryUrl('uploads/violation/' .  $img->filename, \Carbon\Carbon::now()->addMinutes(30)) }}"
                                class="atch-thumb"
                                target="_blank"
                            >
                            <img
                                src="{{ url('/uploads/violation/' .  $img->filename) }}"
                                alt="{{ $img->filename }}"
                                class="img-rounded img-responsive"
                            />
                            </a>
                        </li>
                    @endforeach
                @else
                    <li>
                        <a href="javascript:void(0);" class="atch-thumb">
                            <img 
                                src="{{ url('uploads/violation/no-image-available.png') }}"
                                class="img-responsive img-rounded"
                            />
                        </a>
                    </li>
                @endif
            </ul>
        </div> 
    </div>   
</div>
</div>
<div class="modal-footer hide-footer"  style="display: none">
    <button type="button" class="btn btn-success pull-right" id="violation-edit"><span class="spiner"></span>Submit</button>
</div>
<script>
$('#spacial-note').editable({
    url: `${BaseUrl}/violation/get-violation-for-spacial-notes`,
    params: {
        _token: $('meta[name="csrf_token"]').attr('content')
    },
    success: function(response) {
        new PNotify({
            title: "Violation",
            text: response.message,
            type: "success",
            styling: 'bootstrap3'
        });        
    }
})
</script>