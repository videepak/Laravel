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
		$current = DB::table('subscriptions')->where('id', $p->subscription_id)->first();
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
	 
		<ul class="nav nav-tabs" id="myTab" role="tablist">
		  <li class="nav-item active">
			<a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Summary</a>
		  </li>
		   <li class="nav-item">
			<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Manage Plan</a>
		  </li>
		  <li class="nav-item">
			<a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Payment History</a>
		  </li>
		</ul>
			<div class="tab-content" id="myTabContent">
			
			  <div class="tab-pane fade active in" id="home" role="tabpanel" aria-labelledby="home-tab">
				 
				<table class="table table-striped">
			  <thead>
				 
			  </thead>
			  <tbody>
				<tr>
					<td colspan="2"><b>Profile</b></td>
				</tr>
			    <tr>
				  <td>Company Name</td>
				  <td>@if($subscriber_details){{$subscriber_details->company_name}}@endif</td>
				</tr>
				<tr>
				  <td>Name</td>
				  <td>@if($subscriber_details){{$user->title}}@endif
				  
				  @if(isset($user)){{$user->firstname}}@endif
				  @if(isset($user)){{$user->lastname}}@endif
				  </td>
				</tr>
				<tr>
				  <td>Email</td>
				  <td>@if(isset($user)){{$user->email}}@endif</td>
				</tr>
				<tr>
				  <td>Mobile</td>
				  <td>@if(isset($user)){{$user->mobile}}@endif</td>
				</tr>
				<tr>
				  <td>City</td>
				  <td>@if($subscriber_details){{$subscriber_details->city}}@endif</td>
				</tr>
				<tr>
				  <td>Zip</td>
				  <td>@if($subscriber_details){{$subscriber_details->zip}}@endif</td>
				</tr>
				
				<tr>
					<td colspan="2"><b>Current Plan Info - <span style="color:red">Free Trial Running</span>  </b></td>
				</tr>
				<tr>
				  <td>Plan</td>
				  <td><?php echo $current->package_offering?> $<?php echo $current->price?> <span>/ <?php 
				  if($current->subscription_type=='1'){
						$type = 'Month';
					} else {
						$type = 'Year';
					}
					echo $type;?></span>
					[<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false" onclick="addclass()"><b><small>Manage Plan</small></a>]
					</b> </td>
				</tr>
				<tr>
				  <td>Plan Feature</td>
				  <td><?php echo nl2br($current->features)?></td>
				</tr>
				<tr>
				  <td>Free Trial Start Date</td>
				  <td><?php echo $auth->trial_start?></td>
				</tr>
				<tr>
				  <td>Free Trial End Date</td>
				  <td><?php echo $auth->trial_end?></td>
				</tr> 
				 
				
			  </tbody>
			</table>
			  </div>
			
	<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
	   
		<table class="table table-striped">
		   
		  <tbody>
			<tr>
			  <td>
				<?php $package = DB::table('subscriptions')->whereIn('id', [4])->get();
				foreach($package as $p){
				?>
				<h3><?php echo $p->package_offering?></h3>
				<p><?php
				if($p->subscription_type=='1'){
					$type = 'Month';
				} else {
					$type = 'Year';
				}
				?>
				$<?php echo $p->price?> <span>/ <?php echo $type;?></p>
				
				<p><?php echo nl2br($p->features)?></p>
				
				<?php
				if($current->id==$p->id || $current->id=='2' ||  $current->id=='3'){ 
				
				if($current->id==$p->id){
					$label = 'Current Plan';
				} else {
					$label = 'Downgrade Disable';
				}
				?>
				<p><button type="button" class="btn btn btn-secondary" disabled="disabled">{{$label}}</button></p>
				<?php } else { ?>
				<p><a href="{{url('upgradeplan/'.$p->id)}}" class="btn btn-success" onclick="return confirm('Are you sure want to upgrade plan?')">Upgrade Plan</a></p>
				<?php } ?>
				
				
				
			<form action="{{url('pay/subscription')}}" method="POST" style="margin-top: 13px;">
			<input name="uid" type="hidden" value='<?php echo $auth->id; ?>'>
			<input name="email" type="hidden" value='<?php echo $auth->email; ?>'>
			<input name="amt" type="hidden" value='<?php echo $p->price; ?>'>
			<input name="pack_name" type="hidden" value='<?php echo $p->package_offering; ?>'>
			<input name="subscription_id" type="hidden" value='<?php echo $p->id; ?>'>
			{{ csrf_field() }}
			<script
				src="https://checkout.stripe.com/checkout.js" class="stripe-button"
				data-key="pk_test_ZZ8wxs8ctvUacHIbG3tim8ij00g2lJ7XVS"
				data-amount= "<?php echo $p->price; ?>"
				data-name = "<?php echo $p->package_offering; ?>"
				data-description = "<?php echo $p->package_offering; ?>"
				data-image = "http://trashcanliveapp.devstageserver.com/assets/images/logo.png"
				data-label="Subscribe"
				data-locale = "auto"
				data-currency = "usd" >
			</script>
		</form>
		
		
		
				<?php }?>
				
			  </td>
			  
			  <td>
				<?php $package = DB::table('subscriptions')->whereIn('id', [2])->get();
				foreach($package as $p){
				?>
				<h3><?php echo $p->package_offering?></h3>
				<p><?php
				if($p->subscription_type=='1'){
					$type = 'Month';
				} else {
					$type = 'Year';
				}
				?>
				$<?php echo $p->price?> <span>/ <?php echo $type;?></p>
				
				<p><?php echo nl2br($p->features)?></p>
				
				<?php
				if($current->id==$p->id || $current->id==3){ 
				
				if($current->id==$p->id){
					$label = 'Current Plan';
				} else {
					$label = 'Downgrade Disable';
				}?>
				<p><button type="button" class="btn btn btn-secondary" disabled="disabled">{{$label}}</button></p>
				<?php } else { ?>
				<p><a href="{{url('upgradeplan/'.$p->id)}}" class="btn btn-success" onclick="return confirm('Are you sure want to upgrade plan?')">Upgrade Plan</a></p>
				<?php } ?>
				
				<?php }?>
				
			  </td>
			  
			  <td>
				<?php $package = DB::table('subscriptions')->whereIn('id', [3])->get();
				foreach($package as $p){
				?>
				<h3><?php echo $p->package_offering?></h3>
				<p><?php
				if($p->subscription_type=='1'){
					$type = 'Month';
				} else {
					$type = 'Year';
				}
				?>
				$<?php echo $p->price?> <span>/ <?php echo $type;?></p>
				
				<p><?php echo nl2br($p->features)?></p>
				<?php
				if($current->id==$p->id ){ 
				?>
				<p><button type="button" class="btn btn btn-secondary" disabled="disabled">Current Plan</button></p>
				<?php } else { ?>
				<p><a href="{{url('upgradeplan/'.$p->id)}}" class="btn btn-success" onclick="return confirm('Are you sure want to upgrade plan?')">Upgrade Plan</a></p>
				<?php }
				  }?>
				
				
				
			  </td>
			</tr>
			 
		  </tbody>
		</table>
		</div>


			
			   
			  <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
		<?php
		$auth = Auth::user();
		$subinf = DB::table('subscribers')->where('user_id', $auth->id)->first();
		
		$info = DB::table('subscriptions')->where('id', $subinf->subscription_id)->first();
		?>   
			 <table class="table table-striped">
				<tbody>
				 <tr>
					<td>Plan Name</td>
					<td>Created Date</td>
					<td>Start Date</td>
					<td>End Date</td>
				 </tr>
				<?php
				 if($info->subscription_type=='1'){
					$type = 'Month';
				} else {
					$type = 'Year';
				}
				?>
				 <tr>
					<td><?php echo $info->package_offering?>  $<?php echo $info->price?> <span>/ <?php echo $type;?></td>
					<td><?php
					echo date("d-m-Y H:i:s",strtotime($subinf->created_at));
					?></td>
					<td><?php
					echo date("d-m-Y",strtotime($subinf->sub_start_date));
					?></td>
					<td><?php
					echo date("d-m-Y",strtotime($subinf->sub_end_date));
					?></td>
				 </tr>
			<?php
			$auth = Auth::user();
			$history = DB::table('subscribe_history')->where('user_id', $auth->id)->orderBy('id', 'DESC')->get();
			foreach($history as $h){
				$data = json_decode($h->plan_info);
				$plan_inf = DB::table('subscriptions')->where('id', $data->subscription_id)->first();
			?> 
				 <tr>
					<td><?php echo $plan_inf->package_offering?>
					
					<?php
			if($plan_inf->subscription_type=='1'){
				$type = 'Month';
			} else {
				$type = 'Year';
			}
			?>
			$<?php echo $plan_inf->price?> <span>/ <?php echo $type;?></span>
			</td>
					<td><?php
					echo date("d-m-Y H:i:s",strtotime($data->created_at));
					?></td>
					<td><?php
					echo date("d-m-Y",strtotime($data->sub_start_date));
					?></td>
					<td><?php
					echo date("d-m-Y",strtotime($data->sub_end_date));
					?></td>
				 </tr>
			<?php } ?>
				 
				 
				 
				</tbody>
				</table>
			</div>
		</div>
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

