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
			 @if (session('success'))
				<div class="alert alert-success">
					 {{ session('success') }}
				</div>
				@endif 
		<?php
		$subscriptionid = array(2,3,4);
		$auth = Auth::user();
		$p = DB::table('subscribers')->where('user_id', $auth->id)->first();
		if($auth->trial=='yes'){
			if(date('Y-m-d') > $auth->trial_end){ #free expired
				$current = DB::table('subscriptions')->where('id', $p->subscription_id)->first();
			} else {
				$current = DB::table('subscriptions')->where('id', 28)->first();//free
			}
		}
		$packid[] = $p->subscription_id;
		$diff = array_values(array_diff($subscriptionid, $packid));
		//print_r($diff);
		?>
                <h2>
                    Current Plan -- {{ $current->package_offering}}</h2>
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
	 
		<ul class="nav nav-tabs"  >
		  <li class="nav-item ">
			<a class="nav-link " href="{{url('subscriber-profile')}}">Summary</a>
		  </li>
		   <li class="nav-item">
			<a class="nav-link" href="{{url('manage-plan')}}">Manage Plan</a>
		  </li>
		  <li class="nav-item active">
			<a class="nav-link active"  href="#" >Payment History</a>
		  </li>
		</ul>

		
		<?php
		$auth = Auth::user();
		$subinf = DB::table('subscribers')->where('user_id', $auth->id)->first();
		
		
		?>   
			 <table class="table table-striped">
				<tbody>
				 <tr>
					<td><b>Plan Name</b></td>
					<td><b>Created Date</b></td>
					<td><b>Validity</b></td>
					 
					<td><b>Amount</b></td>
					<td><b>Payment Status</b></td>
					<td><b>Invoice</b></td>
				 </tr>
				 <?php if($auth->trial=='yes'){ ?>
				 <tr>
					<td>Free Trial</td>
					
					<td><?php
					echo date("d-m-Y H:i:s",strtotime($auth->created_at));
					?></td>
					<td>30 Days</td>
					<!--td ><?php
					/*echo date("d-m-Y",strtotime($auth->trial_start));
					?> - <?php
					echo date("d-m-Y",strtotime($auth->trial_end));
					*/?></td-->
					<td><?php echo '$0' ?></td>
					<td>Free</td>
				 </tr>
				 <?php } ?>
				 
			<?php
			$history = DB::table('payment_response')->where('user_id', $auth->id)->get();
			foreach($history as $h){
				$info = DB::table('subscriptions')->where('id', $h->item_number)->first();
				if($info->subscription_type=='1'){
					$type = 'Month';
				} else {
					$type = 'Year';
				}
				?>
				 <tr>
					<td><?php echo $info->package_offering?>  </td>
					<td><?php
					echo date("d-m-Y H:i:s",strtotime($h->created_at));
					?></td>
					<td>1 {{$type}}</td>
					<td>$<?php echo $h->paid_amount;?> </td>
					<td><?php echo $h->payment_status?></td>
					<td><a href="<?php echo $h->receipt?>" target="_blank">View</a></td>
				 </tr>
			<?php } ?>
			 
				 
				 
				 
				</tbody>
				</table>
			 
	  </section>


                
				
				
			
	  
	  
	  
				
            </div>
        </div>
    </div>
</div>
<script>
function addclass(){

	$('ul.nav-tabs li:eq(1)').addClass('active');
	$('ul.nav-tabs li:eq(0)').removeClass('active');
	
}
</script>

@endsection 

