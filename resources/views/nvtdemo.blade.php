@extends('layouts.package_step')
@section('content')
<section class="MainHeading">
	<div class="headingBox">
		<h1>NTV Special Offer!</h1>
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
				
				@if ($errors->any())
				<div class="alert alert-danger">
					<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
				@endif
				<div class="listText">
					<h2>Welcome National Trash Valet Member</h2>
					<ul>
						<li>1st month subscription fee waived.</li>
						<li>30 minute consultation on Trash Scan setup.</li>
						<li>On-demand access to product tutorials and feature updates.</li>
						<li>Watch a Demo - See how Trash Scan App can help your business.</li>
					</ul>
					</div>
				</div>
				<!-- ------------------------------------------------------ -->
			<div class="col-lg-4 col-md-6">
				<div class="sideForm">
				<h3>To get started, sign up now.</h3>
				<p>Please complete all fields</p>
					<form method="post" name="register" action="{{url('signup')}}">
					<!-- --------------------1st row-------------------------- -->
						  <div class="form-row">
					{{ csrf_field() }}
							<div class="form-group col-md-6">
							  <input type="text" class="" name="firstname" placeholder="First Name" required value="{{old('firstname')}}" />
							</div>
							<div class="form-group col-md-6">
							  <input type="text" name="lastname" placeholder="Last Name" required value="{{old('lastname')}}">
							</div>
						  </div>
						  <!-- --------------------2nd row-------------------------- -->
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <input type="text"   placeholder="Job Title" name="job_title" required value="{{old('job_title')}}">
							</div>
							<div class="form-group col-md-6">
							  <input type="text"   placeholder="Phone" required name="phone" value="{{old('phone')}}" minlength="5" maxlength="15">
							</div>
						  </div>
						  <!-- --------------------3rd row-------------------------- -->
						  <div class="form-row">
							<div class="form-group col-md-12">
							  <input type="email"   placeholder="Email" required name="email" value="{{old('email')}}">
							</div>
							<div class="form-group col-md-12">
							  <input type="text" name="company" placeholder="Company" value="{{old('company')}}" required>
							</div>
							<div class="form-group col-md-12">
							  <select name="employee" required>
							  <option value="">--Number of Employees--</option>
							  <option value="1-20" {{ old('employee') == '1-20' ? 'selected' : '' }}>1-20 Employees</option>
							  <option value="21-30" {{ old('employee') == '21-30' ? 'selected' : '' }}>21-30 Employees</option>
							  <option value="31-50" {{ old('employee') == '31-50' ? 'selected' : '' }}>31-50 Employees</option>
							   
							  </select>
							</div>
							<div class="form-group col-md-12" style="display:none">
							  <select name="country" >
							  <option value="">--Select Country--</option>
							 
							<option value="USA" {{ old('country') == 'USA' ? 'selected' : '' }}>USA</option>
							<option value="Other" {{ old('country') == 'Other' ? 'selected' : '' }}>Other </option>
					</select>
							</div>
							<div class=" col-md-12">
							<p> <span>By registering, you agree to the processing of your personal data by 
							Trash Scan as described in <a href="https://subscriber.trashscanapp.com/privacy-policy" target="_blank">Privacy Statement</a>.</span></p>

						     </div>
							 <div class="form-group col-md-12">
							   <input type="Submit" class="submitBtn" value="Submit" style="width:100%"/>
							 </div>
						  </div>
					 </form>
				</div>
			</div>
		</div>
	</div>
</section>
<!-- ------------------------------------------------------ -->

@endsection