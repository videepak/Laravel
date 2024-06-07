@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<link href="{{url('assets/css/bootstrap-editable.css')}}" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.css" rel="stylesheet">
<style>
.parsley-required {
  display:block; 
  padding-left: 15px; 
} 
#example_processing {
        height: 50px !important;
    } 
    .pagination>.active>a,
    .pagination>.active>a:focus,
    .pagination>.active>a:hover,
    .pagination>.active>span,
    .pagination>.active>span:focus,
    .pagination>.active>span:hover {
        color: white !important;
    }
    .set-width {
        width: 205%;
        margin-left: -89%;
    }
    .select2-container--default
    .select2-selection--multiple
    .select2-selection__choice {
        background-color: #3E5566;
        color: white;
    }
    .badge {
        padding: 2px 5px;
        border: 0px solid #1ABB9C !important;
    }
    .dataTables_wrapper .dt-buttons {
        display: none;
    }

</style>
@endsection
@section('content')   

<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
                <h2 style="font-size: initial;">
                    <i class="fa fa-bar-chart"></i> Manage Route Checkpoints
                </h2>
                <ul class="">
                    <li>
                        <a href="{{ url('/report/routecheckpoint-excel/') }}" 
                           class="btn btn-primary pull-right excel-option"
                        >
                        <i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel </a>
                    </li>
                </ul>
              <div class="clearfix"></div>
          </div> 
          <div class="x_content">
                <div class="table-responsive">
                    <table id="route-point" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings"> 
                                <th class="column-title">S.no </th>
                                <th class="column-title">Route Checkpoint </th>
                                <th class="column-title">
                                    <select class="form-control input-sm filter" id="properties">
                                    <option value="">Property</option>
                                    @foreach($properties as $property)
                                    <option value="{{$property->id}}">
                                        {{ucwords($property->name)}}
                                    </option>
                                    @endforeach
                                    </select>
                                </th>
                                <th class="column-title">Checkpoints Scanned </th>
                            </tr>
                        </thead>
                         
                    </table>
                </div>
            </div>
        </div> 
        </div>
    </div>
</div>
 
@endsection 
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
<script>
function loadTable() 
{
    var propertyId = $("#properties").val(); 
    //var building = $("#buildList").val(); 
    $('#route-point').DataTable(
        {
        "bPaginate":true,
        "ordering": false,
        "bLengthChange": true,
        "pageLength": 10,
        "bFilter": false,
        "bInfo": true,
        "bAutoWidth": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": BaseUrl + '/report/get-list',
            "type": "POST",
            "data":{ 
                _token: $('meta[name="csrf_token"]').attr('content'), 
                //building : building,
                propertyId: propertyId,
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "barcode" },
            { "data": "property" },
            { "data": "description" }
        ]
    });
}

$(document).ready(function() {
    loadTable();
    
    $(document).on('change', '.filter', function() {
        let propertyId = $("#properties").val();
        let uel = $(".excel-option").attr('href');
        
        $('.excel-option').attr('href', uel.replace(/\?.+/, ''));
        
        if (propertyId) {
            let uel = $(".excel-option").attr('href');
            $('.excel-option').attr('href', `${uel}?property=${propertyId}`)
        }

        $('#route-point').DataTable().destroy();
        loadTable();
    });
});
</script>
@endsection 
