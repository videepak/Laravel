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
			
			
			<section class="">
	 
		<ul class="nav nav-tabs" >
		  <li class="nav-item active">
			<a class="nav-link active" >Summary</a>
		  </li>
		   <li class="nav-item">
			<a class="nav-link" href="{{url('manage-plan')}}">Manage Plan</a>
		  </li>
		  <li class="nav-item">
			<a class="nav-link"  href="{{url('manage-plan')}}"  >Payment History</a>
		  </li>
		</ul>
		
				 
			<table class="table table-striped">
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
				<?php  
					if(date('Y-m-d') > $p->sub_end_date){
						$label = 'Expired';
					} else {
						$label = 'Running';
					}
					?>
					<td colspan="2"><b>Current Plan Info - <span style="color:red"><?php echo $current->package_offering?> <?php echo $label;?></span>  </b>
					
					<?php if(date('Y-m-d') > $p->sub_end_date){ ?>
					[<a class="nav-link"   href="{{url('manage-plan')}}" ><b><small>Manage Plan to upgrade subscription</small></a>]
					<?php } ?>
					
					</td>
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
					[<a class="nav-link"   href="{{url('manage-plan')}}" ><b><small>Manage Plan</small></a>]
					</b> </td>
				</tr>
				<tr>
				  <td>Plan Feature</td>
				  <td><?php echo nl2br($current->features)?></td>
				</tr>
				
				 
				<tr>
				  <td>Start Date</td>
				  <td><?php echo date("d-m-Y",strtotime($p->sub_start_date))?></td>
				</tr>
				<tr>
				  <td> End Date</td>
				  <td><?php echo date("d-m-Y", strtotime($p->sub_end_date ))?></td>
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

