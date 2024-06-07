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
		$userpack = DB::table('subscribers')->where('user_id', $auth->id)->first();
		
		#current pack
		if($auth->trial=='yes'){
			if(date('Y-m-d') > $auth->trial_end){ #free expired
				$current = DB::table('subscriptions')->where('id', $userpack->subscription_id)->first();
			} else {
				$current = DB::table('subscriptions')->where('id', 28)->first();//free
			}
		}
		
		#upgrade purchase pack
		$p = DB::table('subscriptions')->where('id', $id)->first();
		?> 
                <h2>
                    Current Plan -- {{$current->package_offering}} </h2>
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
		$auth = Auth::user(); ?>
			<table class="table table-striped">
			  <tbody>
				<tr>
					<td>Package Name</td>
					<td>Vallidity</td>
					<td>Period</td>
					<td>Amount</td>
				</tr>
				
				<?php
				#free pack
				if(date('Y-m-d') < $auth->trial_end){
				?>
				<tr>
					<td><b>Free Trial</b></td>
					<td><?php echo TRIAL_DAYS;?> Day</td>
					<td><?php echo date("d-m-Y", strtotime($auth->trial_start)); ?> - <?php echo date("d-m-Y", strtotime($auth->trial_end)); ?></td>
					<td>$0
					
					</td>
				</tr>
				<?php } ?>
				
				
			<?php 
			#current pack
			if($userpack->payment=='1'){
				$today = date("Y-m-d");
				if($today < $userpack->sub_end_date){
				?>
				<tr>
					<td>Current Pack - {{$current->package_offering}}</td>
					<td>1 Month</td>
					<td><?php echo date("d-m-Y", strtotime($userpack->sub_start_date))?> - <?php echo date("d-m-Y", strtotime($userpack->sub_end_date))?></td>
					<td>${{$current->price}}</td>
				</tr>
				<?php }
				} ?>
				
				
				
				
			
			<tr>
			<?php
			#upgrade
			$trial_end = date("d-m-Y", strtotime($auth->trial_end));
			$start = date('d-m-Y', strtotime($trial_end .'+ 1 days'));
			$end = date('d-m-Y', strtotime($trial_end .'+ 30 days'));
			?>
					<td><b>{{$p->package_offering}}</b></td>
					<td>1 Month</td>
					<td><?php echo $start; ?> - <?php echo $end; ?></td>
					<td>${{$p->price}}
					</td>
				</tr>
				<?php
				$balance=0;
				if($userpack->payment=='1'){
				$today = date("Y-m-d");
				if($today < $userpack->sub_end_date){
					
				$now = time(); // or your date as well
				$your_date = strtotime($userpack->sub_start_date);
				$datediff = $now - $your_date;

				$daycount =  round($datediff / (60 * 60 * 24));
				?>	
				<tr>
					<td>Current Plan balance</td>
					<td></td>
					<td></td>
					<td>
					<?php
					$use_amnt = ($current->price/30)*$daycount;
					?>
					$<?php
					$balance = $current->price-$use_amnt;
					echo floor($balance);
					?></td>
				</tr>
				<?php }
				} ?>
				
				
				<tr>
					<td></td>
					<td></td>
					<td>Total</td>
					<td>${{floor($p->price-$balance)}}</td>
				</tr>
				<tr>
					<td colspan="4"><b>This contract will auto-renew immediately following the contract term expiration extending the contract for 1 year until formal termination of services has been initiated as outlined in section 6 of our Service Terms.</b></td>
				</tr>
				<tr>
					<td colspan="4"><p> <input type="checkbox" name="tnc2" id="tnc2"  />  By registering, you agree to the processing of your personal data by 
							Trash Scan as described in <a href="https://subscriber.trashscanapp.com/privacy-policy" target="_blank"><u>Privacy Statement</u></a>. </p>
					</td>
				</tr>
				<tr>
					<td colspan="4"> 
					<p><input type="checkbox" name="tnc" id="tnc"  /> I accept, <a href="{{url('/Mangro-SAAS-Service-Agreement-Generic.pdf')}}" target="_blank"><u>Contract Agreement</u></a>.</p>
					</td>
				</tr>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td>
		<form action="{{url('pay/paysubscribe')}}" method="POST" style="margin-top: 13px;">
			<input name="uid" type="hidden" value='<?php echo $auth->id; ?>'>
			<input name="email" type="hidden" value='<?php echo $auth->email; ?>'>
			<input name="amt" type="hidden" value='<?php  echo floor(($p->price-$balance))*100; ?>'>
			<input name="pack_name" type="hidden" value='<?php echo $p->package_offering; ?>'>
			<input name="subscription_id" type="hidden" value='<?php echo $p->id; ?>'>
			{{ csrf_field() }}
			<script
				src="https://checkout.stripe.com/checkout.js" class="stripe-button"
				data-key="pk_live_xGlO6TP82vQcnwPf1zngA4XO00PN0Unt1a"
				data-amount= "<?php  echo floor($p->price-$balance)*100; ?>"
				data-name = "<?php echo $p->package_offering; ?>"
				data-description = "<?php echo $p->package_offering; ?>"
				data-image = "http://trashcanliveapp.devstageserver.com/assets/images/logo.png"
				data-label="Checkout"
				data-locale = "auto"
				data-currency = "usd" >
			</script>
		</form>
					</td>
				</tr>
				 
				   
			  </tbody>
			</table>
			   
	  </section>


                
		
			
	  
	  
	  
				
            </div>
        </div>
    </div>
</div>
@endsection 

