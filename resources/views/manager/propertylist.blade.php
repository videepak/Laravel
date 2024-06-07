@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<style>  .parsley-required{ display:block; padding-left: 15px; } </style>
<div class="right_col" role="main">

    <div class="x_content">

        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <div class="col-lg-6 col-md-6 col-xs-8 col-sm-8">
                        <h2>Top Violators</h2> 
                    </div>
                    <div class="col-lg-6 col-md-6 col-xs-4 col-sm-4">
                        <div class="col-md-3 pull-right" >
                            <select name='top' class="form-control input-sm pull-right top" style="width: auto !important;" data-proid="">
                                <option value="5" @if(isset($top) && $top == 5) selected @endif>Top 5</option>
                                <option value="10" @if(isset($top) && $top == 10) selected @endif>Top 10</option>
                                <option value="20" @if(isset($top) && $top == 20) selected @endif>Top 20</option>
                                @if(isset($propertyId) && !empty($propertyId))
                                <option value="" >All</option>
                                @endif
                            </select>
                        </div>

                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">

                    <div class="accordion" id="accordion1" role="tablist" aria-multiselectable="true">
                        @foreach($properties as $property)
                              
                        <div class="panel main-div" >
                            <a class="panel-heading get-unit-ajax" 
                               data-id="{{$property['id']}}" role="tab" 
                               id="headingTwo1" data-toggle="collapse" 
                               data-parent="#accordion1" 
                               href="#collapse{{ $loop->iteration }}" aria-expanded="true" 
                               aria-controls="collapseTwo">
                                <span class="panel-title property-name" style="font-size: 13px;">
                                    <b>#{{$property['id'].' - '.ucwords($property['name'])}}</b>
                                    <span class="pull-right">
                                        <b>
                                            Total Violation: 
                                            {{$property['get_violation_by_properties_count']}}
                                        </b>
                                    </span>
                                </span>
                            </a>
                            <div id="collapse{{ $loop->iteration }}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo" aria-expanded="true" style="">
                                <div class="panel-body view-part" ></div>
                            </div>
                        </div>
                        @endforeach



                    </div>

                </div>
            </div>
        </div>


    </div>       
</div>
@endsection 
@section('js')
<script>

    $('span.panel-title').click(function (event) {
        event.preventDefault();
        event.stopPropagation();
        //console.log('Custom Function');
        return false;
    });

    var base_url = '{{url('')}}';

    $('.property-name').click(function () {

        var win = window.open(base_url + '/top-violation/' + $(this).parent().data('id'), '_blank');
        win.focus();
        // window.location.href = base_url + '/property-manager/top-violation/' + $(this).parent().data('id');
    });

    $('.get-unit-ajax').click(function () {
        $('.top').attr('data-proid', $(this).data('id'));
    });

    $('.get-unit-ajax, .top').click(function (event) {

        //alert($('.top').attr('data-proid'));
        $.ajax({
            url: base_url + '/property-manager/get-violation',
            type: 'POST',
            data: {"_token": "{{ csrf_token() }}", "id": $('.top').attr('data-proid'), "top": $(".top").val()},
            beforeSend: function () {
                $('.view-part').html('<div class="col-md-12 col-sm-12 col-xs-12" style="padding: 40px 15px;text-align: center;"><i class="fa fa-spinner fa-spin" style="font-size:30px"></i></div>');
            },
            success: function (data) {
                //alert(data);
                $('.view-part').html(data);
            }
        });
    });


</script>
@endsection





