@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
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
        <div class="x_panel">
            <div class="x_title">
                <h2>Building List </h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="table-responsive">
                    <table id="example" class="table table-striped jambo_table bulk_action">
                        <thead>
                            <tr class="headings"> 
                                <th class="column-title">S.no </th>
                                <th class="column-title">Name </th>
                                <th class="column-title">Type </th>
                                <th class="column-title">Building </th>
                                <!-- <th class="column-title">Units </th> -->
                                <th class="column-title">Address </th>
                                <!-- <th class="column-title">City</th>
                                <th class="column-title">State</th>
                                <th class="column-title">Zip</th>
                                <th class="column-title">Action</th> -->
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="deleteProperty" action="" method="POST">
    {{method_field('DELETE')}}
    {{ csrf_field() }}
</form>
@endsection 
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/building.list.js')}}"></script>
<script>

    function deleteProperty(element, event)
    {
        event.preventDefault();
        if (confirm('Are you sure you want to continue?'))
        {
            var url = $(element).attr('href');
            $('#deleteProperty').attr('action', url);
            $('#deleteProperty').submit();
        }
    }
    
</script>
@endsection





