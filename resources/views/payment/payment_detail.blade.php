@extends('layouts.user_app')
@extends('layouts.user_menu')
@section('content')
<style>
    .parsley-required{
        display: block;
    }
</style>   
<div class="right_col" role="main">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>
                    Payment Detail</h2>
                <div class="clearfix"></div>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="x_content">



                <br>
                <form id="add_customer" name="add_customer" data-parsley-validate="" class="form-horizontal form-label-left" novalidate="" action="" method="post">
                    
                    <div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="title_field">Transaction ID
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							xxxxxxxx
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="title_field">Payment Date
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							4-3-2020
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="email_field">Amount 
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							$75
						</div>
					</div>

					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="phone">Package 
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							Month to Month
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="phone">Method
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							Visa Credit Card
						</div>
					</div>
					<div class="form-group">
						<label class="control-label col-md-3 col-sm-3 col-xs-12" for="phone">Card No.
						</label>
						<div class="col-md-6 col-sm-6 col-xs-12">
							xxxxxxxxxxxx
						</div>
					</div>

				  
                     

                </form>
				
				
            </div>
        </div>
    </div>
</div>
@endsection 

