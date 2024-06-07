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
		<?php
		$auth = Auth::user();
		?>
                <h2>
                    Thanks for Payment</h2>
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
			
			
	 <section class="tabCtrl">
		<?php
		$auth = Auth::user();
		$payinfo = DB::table('payment_response')->where('id', $payid)->first();
		$response = json_decode($payinfo->payment_data);
		//echo '<pre>';
		//print_r($response);
		?>
			<table class="table table-striped">
			  <tbody>
				<tr>
					<td>Amount Paid</td>
					<td>${{$payinfo->paid_amount}}</td>
				</tr>
				<tr>
					<td>Email Id</td>
					<td>{{$payinfo->email}}</td>
				</tr>
				 
				<tr>
					<td>Package Name</td>
					<td>{{$payinfo->item_name}}</td>
				</tr>
				<tr>
					<td>Payment Status</td>
					<td>
					<?php
					echo $payinfo->payment_status
					?></td>
				</tr>
				<tr>
					<td>Payment Date</td>
					<td>
					<?php
					echo $payinfo->created_at
					?></td>
				</tr>
				<tr>
					<td>Payment Receipt</td>
		<td><a href="<?php echo $payinfo->receipt?>" target="_blank">View  Receipt</a></td>
				</tr>
				 
				   
			  </tbody>
			</table>
			   
	  </section>


                
		
			
	  
	  
	  
				
            </div>
        </div>
    </div>
</div>
@endsection 

