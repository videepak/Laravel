@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')   
        <div class="right_col" role="main">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Manage Templates </h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li>
                                <a class="btn btn-primary" 
                                   href="javascript:void(0);"
                                   data-toggle="modal" 
                                   data-target="#add-template"
                                   >
                                    + Add Template 
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
                                        <th class="column-title violation-head">S.No</th>
                                        <th class="column-title violation-head">Name</th>
                                        <th class="column-title violation-head">Subject</th>
                                        <th class="column-title violation-head">Default</th>
                                        <th class="column-title violation-head">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)

                                    <tr class="even pointer">  
                                        <td>{{$offset++}}</td>
                                        <td width="20%">
                                            @isset($template->name)
                                            {{ucfirst($template->name)}}
                                            @endisset
                                        </td>
                                        <td width="50%">
                                            @isset($template->subject)
                                            {{ucfirst($template->subject)}}
                                            @endisset
                                        </td>
                                        <td>
                                            @if(!$template->status)
                                            <a href="{{url('set-template-status/'.$template->id.'')}}"
                                               title='Set Default Template'
                                               class="print-view">
                                                <li class="fa fa-check-circle-o fa-lg" style="color:gray;"></li>
                                            </a>
                                            @else
                                            <a href="javascript:void(0);">
                                                <li class="fa fa-check-circle fa-lg" style="color:green;"></li>
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);"
                                               class="get-detail"
                                               title='Edit'
                                               data-id ="{{$template->id}}">
                                                <li class="fa fa-edit"></li></a>
                                            @if(!$template->status)
                                            <a href="{{url('delete-template/'.$template->id)}}"
                                               title='Delete' 
                                               onclick="return confirm('Are you sure you want to delete the template?')"
                                               >
                                                <li class="fa fa-trash-o"></li>
                                            </a>
                                            @endif

                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <span style="float: right;">
                        {{$templates->links()}}
                    </span>
                </div>
            </div>
        </div>
<!--Model for add template: Start-->
<div id="add-template" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Add Templates</h4>
            </div>
            <form class="form-horizontal form-label-left" 
                  action="{{url('add-template')}}"
                  method="post" id="violatio-template">
                
                <input type="hidden" name="_token" id="_token" value="{{ csrf_token() }}"/>
                
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <input type="text" 
                                   id="name" 
                                   name="name" required 
                                   class="form-control validate-space" 
                                   placeholder="Name">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <input type="text" 
                                   id="subject" 
                                   name="subject" required 
                                   class="form-control validate-space" 
                                   placeholder="Subject">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <textarea name="content" id="template" 
                                      class="form-control col-md-7 col-xs-12 validate-space" 
                                      rows="5" 
                                      placeholder="Write here.."
                                      cols="40" required></textarea>
                        </div>
                    </div>
                    
                    <input type="hidden" 
                           name="template_id" required value="1"
                           class="form-control validate-space" />
                    
                    <input type="hidden" 
                           name="subscriber_id" required value="{{$user->subscriber_id}}"
                           class="form-control validate-space" />
                    
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
@endsection 
@section('js')
<script src="{{url('assets/trashscanjs/violation.template.list.js')}}"></script>
@endsection