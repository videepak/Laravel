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
			 @if (session('message'))
				<div class="alert alert-success">
					 {{ session('message') }}
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
		$pay_status = $p->payment;
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
			
			
			<section class="">
	 
		<ul class="nav nav-tabs" >
		  <li class="nav-item">
			<a class="nav-link " href="{{url('subscriber-profile')}}">Summary</a>
		  </li>
		   <li class="nav-item active">
			<a class="nav-link active"  href="#">Manage Plan</a>
		  </li>
		  <li class="nav-item">
			<a class="nav-link " href="{{url('payment-history')}}">Payment History</a>
		  </li>
		</ul>
	   
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
				
				<div style="height:220px;"><?php echo nl2br($p->features)?></div>
				
				<?php
				
			if($pay_status!='0'){
				
				
				if($current->id==$p->id || $current->id=='2' ||  $current->id=='3'){ 
				
				if($current->id==$p->id){
					$label = 'CURRENT';
				} else {
					$label = 'DOWNGRADE';
				}
				?>
				<p style="padding-left:30px;"><button type="button" class="btn btn btn-secondary" disabled="disabled">{{$label}}</button></p>
				<?php } else { ?>
				<p style="padding-left:30px;"><a href="{{url('upgradeplan/'.$p->id)}}" class="btn btn-success" onclick="return confirm('Are you sure want to upgrade plan?')">UPGRADE</a></p>
				<?php } ?>
				
			<?php } else {?>	
				<p><a href="{{url('upgradeplan/'.$p->id)}}" class="btn btn-success" >SUBSCRIBE</a></p>
			
			<?php } ?>
		
		
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
				
				<div style="height:220px;"><?php echo nl2br($p->features)?></div>
				
				<?php
			if($pay_status!='0'){
				if($current->id==$p->id || $current->id==3){ 
				
				if($current->id==$p->id){
					$label = 'CURRENT';
				} else {
					$label = 'DOWNGRADE';
				}?>
				<p style="padding-left:30px;"><button type="button" class="btn btn btn-secondary" disabled="disabled">{{$label}}</button></p>
				<?php } else { ?>
				<p style="padding-left:30px;"><a href="{{url('upgradeplan/'.$p->id)}}" class="btn btn-success" onclick="return confirm('Are you sure want to upgrade plan?')">UPGRADE</a></p>
				<?php } ?>
				
			<?php } else{ ?> 
			
			<p style="padding-left:30px;"><a href="{{url('upgradeplan/'.$p->id)}}" class="btn btn-success" >SUBSCRIBE</a></p>
			
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
				
				<div style="height:220px;"><?php echo nl2br($p->features)?></div>
				<?php
			if($pay_status!='0'){
				if($current->id==$p->id ){ 
				?>
				<p style="padding-left:30px;"><button type="button" class="btn btn btn-secondary" disabled="disabled">CURRENT</button></p>
				<?php } else { ?>
				<p style="padding-left:30px;"><a href="{{url('upgradeplan/'.$p->id)}}" class="btn btn-success" onclick="return confirm('Are you sure want to upgrade plan?')">UPGRADE</a></p>
				<?php }
				} else { ?>
				
				<p style="padding-left:30px;"><a href="{{url('upgradeplan/'.$p->id)}}" class="btn btn-success" >SUBSCRIBE</a></p>
				
				<?php }
				  } ?>
				
				
				
			  </td>
			</tr>
			 
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

