<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title></title>
        <style>
            table.content-det tr th {
                padding: 5px;
                text-align: left;
            }
            #table tr td {
                padding: 5px;
            }
            #break-page {
                display: block;
                page-break-before: always;     
            }
        </style>
    </head>

    <body style="border: 3px solid black;">
        <div style="text-align:center;margin-top: 50px">
            <img src="{{$logo}}" style="border: 1px solid #ddd;border-radius: 4px;display: block;max-width:250px;max-height:100px;width: auto;height: auto;" width="400" height="400" />
            <br/><br/>
            <b><u>OBSERVATION VIOLATION REPORT</u></b>
        </div>
        <div style="margin: 20px 20px 0 20px;">

                    @foreach($violations as $violation)
                    <div class="row" @if($loop->index)id="break-page"@endif>

                    <table style="width:100%" cellspacing='0' border='1px' class="content-det">
                            
                            @if(!empty($violation['status']))
                            <tr>
                                <th width="25%">Violation Status:</th>
                                <td width="75%">
                                    {{$violation['status']}}
                                </td>
                            </tr>
                            @endif

                            @if(!empty($violation['property_name']))
                            <tr>
                                <th>Property Name:</th>
                                <td>
                                    @if($violation['property_name'])
                                        {{$violation['property_name']}}
                                    @endif
                                </td>
                            </tr>
                            @endif

                            @if(!empty($violation['address']))
                            <tr>
                                <th>Property Address:</th>
                                <td>
                                   {{$violation['address']}}
                                </td>
                            </tr>
                            @endif

                            @if(!empty($violation['unit']))
                            <tr>
                                @if($violation['isRoute'])
                                    <th>Route Checkpoint :</th>
                                @else
                                    <th>Bin Tag ID:</th>
                                @endif
                                <td>
                                   {{$violation['unit']}}
                                </td>
                            </tr>
                            @endif


                            @if(isset($violation['type']) && $violation['type'] == 2)
                                @if(!empty($violation['building']))
                                    <tr>
                                        <th>Streets : </th>
                                        <td>{{$violation['building']}}</td>
                                    </tr>
                                @endif

                                @if(!empty($violation['building_address']))
                                    <tr>
                                        <th>Streets Address :</th> 
                                        <td>{{$violation['building_address']}}</td>
                                    </tr>
                                @endif 
                            @elseif(isset($violation['type']) && $violation['type'] == 4)
                                @if(!empty($violation['building']))
                                    <tr>
                                        <th>Buildings :</th>
                                        <td>{{$violation['building']}}</td>
                                    </tr>
                                @endif

                                @if(!empty($violation['building_address']))
                                    <tr>
                                        <th>Buildings Address :</th> 
                                        <td>{{$violation['building_address']}}</td>
                                    </tr>
                                @endif
                            @elseif(isset($violation['type']) && $violation['type'] == 3)
                                @if(!empty($violation['building']))
                                <tr>
                                    <th>Floors :</th> 
                                    <td>{{$violation['building']}}</td>
                                </tr>
                                @endif

                                @if(!empty($violation['building_address']))
                                    <tr>
                                        <th>Floors Address :</th> 
                                        <td>{{$violation['building_address']}}</td>
                                    </tr>
                                @endif
                            @else
                                @if(!empty($violation['building']))
                                    <tr>
                                        <th>Buildings :</th>
                                        <td>{{$violation['building']}}</td>
                                    </tr>
                                @endif

                                @if(!empty($violation['building_address']))
                                    <tr>
                                        <th>Buildings Address :</th> 
                                        <td>{{$violation['building_address']}}</td>
                                    </tr>
                                @endif
                            @endif





                            <tr>
                                <th>Reason:</th>
                                <td>{{$violation['reason']}}</td>
                            </tr>
                            <tr>
                                <th>Action:</th>
                                <td>{{$violation['action']}}</td>
                            </tr>
                            @if(isset($violation['special_note']) && !empty($violation['special_note']))
                            <tr>
                                <th>Special Note:</th>
                                <td>{{$violation['special_note']}}</td>
                            </tr>
                            @endif
                            <tr>
                                <th>Date:</th>
                                <td>    
                                    {{\Carbon\Carbon::parse($violation['created_at'])->timezone(getUserTimezone())->format('m-d-Y h:i A')}}                            
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                </div>
                @if(count($violation['images']))
                    <div style="margin-top: 40px">     
                        @php $imgCount = 0; @endphp
                        <table cellspacing=10 >
                            <tr>
                            @foreach($violation['images'] as $img)
                                @if(Storage::disk('s3')->exists('uploads/violation/'.$img->filename) && !empty($img->filename))
                                    <td>
                                        <img
                                            src="{{ Storage::disk('s3')->temporaryUrl('uploads/violation/'.$img->filename, \Carbon\Carbon::now()->addMinutes(10))}}"
                                            style="border: 1px solid #ddd;border-radius: 4px;padding: 10px;"
                                            width="300px"
                                            height="350px"
                                        />
                                    </td>
                                    @php 
                                        $imgCount++; 
                                        if ($imgCount%2 == 0) {
                                            echo "</tr><tr>";
                                        }
                                    @endphp
                                @endif
                            @endforeach
                                </tr>
                        </table>
                    </div>
                @endif
            @endforeach
            @if(isset($violation['reminder']) && !empty($violation['reminder']))            
                <p>
                    <b>Please Remember :</b> 
                    {!!html_entity_decode($violation['reminder'])!!}
                </p>
            @endif    
                <p>
                    <b>Property Management</b> <span style="width:200px;height:20px;border-bottom:1px solid #000;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> (Signature)</p>
                <p>
                
                @if(isset($violation['comment']) && !empty($violation['comment']))
                    <p>
                        <b>Comment :</b> 
                            {{$violation['comment']}}
                    </p>
                @endif    
    </body>
</html>