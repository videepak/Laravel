@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('css')
<!-- Datatables -->
<link href="{{url('assets/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css')}}" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/1.6.3/css/buttons.dataTables.min.css" rel="stylesheet">
<style>
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
    .dataTables_wrapper .dt-buttons {
        display: none;
    }
    .upload-btn-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
    }
  
  .btn {
    border: 1px solid #169F85;
    color: #fff;
    background-color: #26B99A;
    padding: 5px 5px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: inherit;
    }
  
  .upload-btn-wrapper input[type=file] {
    font-size: 100px;
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    }
    .sendResident{
        color: red;
    }
    span.tag {
        background: #3E5566;
    }
</style>
@endsection
@section('content')       
@yield('menu')     
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Email History - {{ $resName->full_name }}</h2>
                <div class="clearfix"></div>
            </div>

            <div class="x_content">
                <div class="table-responsive">
                    <table id="email_history" class="table table-striped jambo_table bulk_action" data-id={{ $emailHistory }}>
                        <thead>
                            <tr class="headings">
                                <th class="column-title">S.No</th>
                                <th class="column-title">Unit Name</th>
                                <th class="column-title">Template Name</th>
                                <th class="column-title">Subject</th>
                                <th class="column-title">CC</th>
                                <th class="column-title">Body</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>  


@endsection 
@section('js')
<script src="{{url('assets/vendors/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/vendors/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
<script src="{{url('assets/trashscanjs/resident.list.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/dataTables.buttons.min.js
"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js
"></script>
<script src="https://cdn.datatables.net/buttons/1.6.3/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js" integrity="sha512-rstIgDs0xPgmG6RX1Aba4KV5cWJbAMcvRCVmglpam9SoHZiUCyQVDdH2LPlxoHtrv17XWblE/V/PP+Tr04hbtA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection 