@extends('layouts.package_step')
@section('content')

<section class="MainHeading">
	<div class="headingBox">
		<h1>Confirm free trial order</h1>
	</div>
</section>
<!-- ------------------------------------------------------ -->
<section class="textwithform">
	<div class="container">
			<div class="row">
				<div class="col-lg-8 col-md-6">
				@if (session('error'))
				<div class="alert alert-danger">
					{{ session('error') }}
				</div>
				@endif
				<div class="listText">
				
				@php
				$p = DB::table('subscriptions')->where('id', '28')->first();
				if($p->subscription_type=='1'){
						$pt = 'Month';
					} else {
						$pt = 'Year';
					}
				$today = date('d-m-Y');
				@endphp
					
					<?php echo $p->plan_content; ?>
					 
				</div>
				</div>
				<!-- ------------------------------------------------------ -->
			<div class="col-lg-4 col-md-6">
				<div class="sideForm">
				<h3>Please complete all fields</h3>
				
				<p>You have selected - {{$p->package_offering}} $<?php echo $p->price?>/<?php echo $pt?></p>
				<p>Free Trail Start Date : <?php echo date('d-m-Y')?> End Date: <?php echo date('d-m-Y', strtotime($today .'+ '.TRIAL_DAYS.' days'))?></p>	
					<form method="post" name="register" action="{{url('subscription_pack')}}">
					{{ csrf_field() }}
					<!-- --------------------1st row-------------------------- -->
						  <div class="form-row">
						  <input type="hidden" name="pack_id"  value="@php  echo ($udata->subscription_id); @endphp"  />
						  
							<div class="form-group col-md-6">
							  <input type="text" name="firstname" class="form-control"  placeholder="Rick" value="@php  echo ($udata->firstname); @endphp" required>
							</div>
							<div class="form-group col-md-6">
							  <input type="text" name="lastname" placeholder="Johnson" value="@php  echo ($udata->lastname); @endphp" required>
							</div>
						  </div>
						  <!-- --------------------2nd row-------------------------- -->
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <input type="text" name="job_title" placeholder="Job Title" value="@php  echo ($udata->job_title); @endphp" required>
							</div>
							<div class="form-group col-md-6">
							  <input type="text" name="phone"  placeholder="800776655" value="@php  echo ($udata->mobile); @endphp" required>
							</div>
						  </div>
						  <!-- --------------------3rd row-------------------------- -->
						  <div class="form-row">
							<div class="form-group col-md-12">
							  <input type="email" placeholder="rick@gmail.com" name="email" value="@php  echo ($udata->email); @endphp" required readonly style="background:#ccc"/>
							</div>
							<div class="form-group col-md-12">
							  <input type="text" name="company"  placeholder="ABC ventures Inc" value="@php  echo ($udata->company); @endphp" required>
							</div>
							<div class="form-group col-md-12">
							 <select name="employee" required>
							  <option value="">--Select Field Employee--</option>
							  <option value="1-20" @php if($udata->employee=='1-20') { echo 'selected="selected"';} @endphp  >1-20 Employees</option>
							  <option value="21-30" @php if($udata->employee=='21-30') { echo 'selected="selected"';} @endphp >21-30 Employees</option>
							  <option value="31-50" @php if($udata->employee=='31-50') { echo 'selected="selected"';} @endphp  >31-50 Employees</option>
							  </select>
							</div>							
							<div class="form-group col-md-12">
							  <input type="password" name="pwd" id="pwd"  placeholder="Password" value="" required minlength="6">
							</div>
							<div class="form-group col-md-12">
							  <input type="password" name="pwd2"id="pwd2"   placeholder="Confirm Password" value="" required>
							</div>
							
							
							<div class=" col-md-12">
							<p> <span><input type="checkbox" name="tnc2" id="tnc2" required="required"/>  By registering, you agree to the processing of your personal data by 
							Trash Scan as described in <a href="https://subscriber.trashscanapp.com/privacy-policy" target="_blank">Privacy Statement</a>.</span></p>
						     </div>
						 <div class=" col-md-12">
						 <p><span><input type="checkbox" name="tnc" id="tnc" required="required"/> I accept, Free Trial <a href="{{url('/Mangro-SAAS-Service-Agreement-Generic.pdf')}}" target="_blank">Contract Agreement</a>.</span></p>
						 </div>
							 <div class="form-group col-md-12">
							   
							   <input type="Submit" class="submitBtn" value="Confirm Free Trial Order" style="width:100%" onclick="return frm_validate()"/>
							 </div>
						  </div>
					 </form>
				</div>
			</div>
		</div>
	</div>
</section>
<script >
function frm_validate(){
	var pwd = $('#pwd').val();
	var pwd2 = $('#pwd2').val();
	if(pwd.trim() != pwd2.trim()){
		alert('Confirm Password not matched.');
		return false;
	}
	
}
</script>
@endsection