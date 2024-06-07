@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<style>  .parsley-required{ display:block; padding-left: 15px; } </style>
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <div class="col-lg-6 col-md-6 col-sm-8 col-xs-8">
                    <h2>Property List Check In</h2>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings"> 
                                <th class="column-title">S.no </th>
                                <th class="column-title">Property Detail </th>
                                <th class="column-title">Check-In</th>
                                <th class="column-title">Check-Out</th>
                                <th class="column-title">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($properties) && ($properties->isNotEmpty()))
                            @foreach($properties as $key=>$property)
                            <tr class="even pointer"> 
                                <td>{{$offset++}}</td>
                                <td> 
                                    <a href="javascript:void(0);" data-remote="{{url('get-employee/'.$property->id.'')}}" data-toggle="modal" data-target="#get-employee">
                                        <b>Name:</b>
                                        {{ucwords($property->name)}}

                                        <br/> <b>Address:</b>
                                        {{$property->address}}, <br/>{{$property->city}}, 
                                        
                                        @if(isset($property->state) && !empty($property->getState))
                                            {{$property->getState->name}}
                                        @endif

                                        {{$property->zip}}
                                        
                                        <br/> <b>Type:</b> 
                                        
                                        @if($property->type == 1)
                                            Curbside Community
                                        @elseif($property->type == 2)
                                            Garden Style Apartment 
                                        @elseif($property->type == 3)
                                            High Rise Apartment 
                                        @elseif($property->type == 4)
                                            Townhome 
                                        @endif
                                    </a>
                                </td>

                                <td>
                                    @if ($property->allcheckIn->first()->check_in ?? 0)
                                    
                                    {{ $property->allcheckIn->first()->created_at->timezone(getUserTimezone())->format('m-d-Y h:i A') }}
                                    
                                    @endif
                                </td>
                                <td>
                                    @if ($property->allcheckIn->first()->check_in_complete ?? 0)
                                    
                                    {{ $property->allcheckIn->first()->updated_at->timezone(getUserTimezone())->format('m-d-Y h:i A') }}
                                    
                                    @endif
                                </td>

                                <td>
                                    @if(!$property->check_in_property_count)
                                        <a href="javascript:void(0);" title="Contact Assigned Porters" data-proId="{{$property->id}}" class="send-sms-btn">
                                            <i class="fa fa-mail-forward"></i>
                                        </a>
                                    @endif

                                    @if($property->check_in_property_count)
                                    <a href="javascript:void(0);" data-remote="{{url('get-employee/'.$property->id.'')}}" data-toggle="modal" data-target="#get-employee">
                                                <i class="fa fa-user"></i>
                                        </a>
                                    @endif
                                </td>  
                            </tr>
                            @endforeach 
                            @endif 
                        </tbody>
                    </table>
                </div>
                <span style="float: right;">{{ $properties->links() }}</span>
            </div>
        </div>
    </div>
</div>       

<!-- Employee list Modals: Start -->
<div id="get-employee" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content"></div>
    </div>
</div>
<!--Employee list Modals: End -->
<!-- Send sms modals: Start -->
<div id="send-sms" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 500px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title" id="myModalLabel2">Contact Assigned Porters</h4>
            </div>
            <div class="modal-body">

                <span id="msg"></span>

                <form id="demo-form" data-parsley-validate="" novalidate="">
                    <div class="form-group" id="message-box">
                        <label for="message">Message:</label>
                        <textarea id="message" required="required" class="form-control" name="message" ></textarea>
                    </div>
                    <div class="form-group">
                        <label for="message">Employee List:</label>
                        <div class="" id="employees-list"></div>
                    </div>
                    <input type="hidden" id="property-id-val">
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary send-msg"><i class="fa fa-spinner fa-spin" id="loading" aria-hidden="true" style="display: none;"></i>Send</button>
            </div>
        </div>
    </div>
</div>
<!--Send sms modals:: End -->
@endsection 
@section('js')
<script>
    $(document).ready(function () {

        var baseUrl = '{{url('')}}';
        var token = "{{ csrf_token() }}";

        $('.send-sms-btn').click(function () {
            $('#property-id-val').val($(this).data('proid'));
            var proId = $(this).data('proid');

            $.ajax({
                url: baseUrl + '/send-sms-employee',
                type: "POST",
                data: {
                    _token: token,
                    proId: proId
                },
                success: function (data) {
                    $('#employees-list').html(data);
                    $('#message-box').show();
                    $('.send-msg').show();

                    if (data == 'false') {
                        $('#employees-list').html('<h6 style="color:red;">No employee assigned to this property.</h6>');
                        $('.send-msg').prop('disabled', true);
                    } else {
                        $('.send-msg').prop('disabled', false);
                    }

                    $('#send-sms').modal('show');
                }
            });
        });

        $('.send-msg').click(function () {

            var message = $('#message').val();
            var proId = $('#property-id-val').val();
            var checkedValues = $('#employee-id:checked').map(function () {
                return this.value;
            }).get();

            $.ajax({
                url: baseUrl + '/check-in-sms',
                type: "POST",
                data: {_token: token, proId: proId, message: message, checkedValues: checkedValues},
                beforeSend: function () {
                    $('.send-msg').prop('disabled', true);
                    $('#loading').css('display', 'block');
                },
                success: function (data) {

                    $('#loading').css('display', 'none');
                    $('.send-msg').prop('disabled', false);

                    if (data == 'true') {
                        $('#msg').html('<div class="alert alert-success"><strong>Message Sent Successfully</strong></div>');
                        setTimeout(function () {
                            location.reload();
                        }, 3000);
                    } else {
                        $('#msg').html(data);
                    }
                }
            });
        });

        $('#send-sms').on('hidden.bs.modal', function () {
            $(this).find('form').trigger('reset');
            $('#msg').html("");
            //$('#employees-list').html("");
        })
    });
</script>
@endsection





