<style>
.attachment ul li {
    width: 263px;
}
.attachment ul li img {
    height: 248px;
    border: 1px solid #ddd;
    padding: 5px;
    margin-bottom: 10px;
}
</style>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><strong id="popup-heading">Violation Image</strong></h4>
</div>
<div class="modal-body main-div">
    <div class="container" style="margin-left: 3%;">
        <div class="attachment">
            <ul>
                @if(count($violation['images']))
                    @foreach($violation['images'] as $img)
                        <li>
                            <a 
                                href="{{ url('uploads/violation/' .  $img->filename) }}"
                                class="atch-thumb"
                                target="_blank"
                            >
                            <img
                                src="{{ url('uploads/violation/' .  $img->filename) }}"
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