@extends('layouts.user_app')
@extends('layouts.menu')
@section('content')
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Subscription List </h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li>
                        <a class="btn btn-primary"
                           href="{{url('admin/addsubscription')}}">
                            + Add Subscription
                        </a>
                    </li>
                </ul>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">



                <div class="table-responsive">
                    <table class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings">                            
                                <th class="column-title">Packages </th>
                                <th class="column-title">Package Admins </th>
                                <th class="column-title">Field Collector </th>
                                <th class="column-title">QR Codes </th>
                                <th class="column-title">Subscription Type </th>
                                <th class="column-title">Price </th>
                                <th class="column-title">Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscription as $sublist)
                            <tr class="even pointer">                            
                                <td class=" ">{{$sublist->package_offering}}</td>
                                <td class=" ">{{$sublist->package_admin}}</td>
                                <td class=" ">{{$sublist->package_field_collector}}</td>
                                <td class=" ">{{$sublist->package_qr_code}}</td>   
                                <td class=" ">@if($sublist->subscription_type==1)Monthly @elseif($sublist->subscription_type==12)Yearly @endif</td>   
                                <td class=" ">$ {{$sublist->price}}</td>   
                                <td class=" "><a href="{{url('admin/deletesubscription')}}/{{$sublist->id}}"  onclick="return confirm('Are you sure you want to continue')" ><li class="fa fa-trash-o"></li></a>  
                                    <a href="{{url('admin/viewsubscription')}}/{{$sublist->id}}"><li class="fa fa-edit"></li></a>
                                </td>   
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>


            </div>
        </div>
    </div>
</div>
@endsection 

