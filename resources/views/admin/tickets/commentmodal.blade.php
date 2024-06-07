<div class="col-md-7 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_content" style="width: 100%;height: 400px;overflow: auto;position:relative;z-index: 9999;">
            @if($status->comment->isNotEmpty())
                <ul class="list-unstyled msg_list">
                    @foreach($status->comment as $statu)
                        <li>
                            <a>
                                <span>{{ucwords($statu->getAdmin->name)}}</span>
                                <span class="time">
                                {{\Carbon\Carbon::parse($statu->created_at)->timezone(getUserTimezone())->format('m-d-Y h:m A')}}
                                </span>
                                </span>
                                <span class="message">
                                {{ucwords($statu->comment)}}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <h2>No Comment Found</h2>    
            @endif    
        </div>
    </div>
</div>
 <div class="col-md-5 col-sm-6 col-xs-12">
    <div class="x_panel">
        <div class="x_content" style="position: relative;z-index: 9999;">
                @if(!empty($status->ticketCategory->category_name))
                    <b>Category : </b>{{ucwords($status->ticketCategory->category_name)}}
                    <br/>
                @endif
                
                <b>Message : </b>{{ucwords($status->message)}}<br/> 
                              
                <br/><br/>
                @if(!empty($status->files_name) && $status->files_type == 'image')
                    <p>
                        <a href="{{url('/uploads/tickets')}}/{{$status->files_name}}" alt="{{ucwords($status->files_name)}}" target="_blank">
                            <img 
                                src="{{url('/uploads/tickets')}}/{{$status->files_name}}" alt="{{ucwords($status->files_name)}}"
                                class="img-thumbnail"
                            />
                        </a>        
                    </p>
                @endif

                @if(!empty($status->files_name) && $status->files_type == 'video')
                <a href="{{url('/uploads/tickets')}}/{{$status->files_name}}" alt="{{ucwords($status->files_name)}}" target="_blank">
                    <p>
                        <video width="320" height="240" controls>
                            <source 
                                src="{{url('/uploads/tickets')}}/{{$status->files_name}}" type="video/mp4" />
                            Your browser does not support the video tag.
                        </video>
                    </p>
                </a>    
                @endif 
        </div>
    </div>
</div>
