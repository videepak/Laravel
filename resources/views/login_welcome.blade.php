@extends('layouts.package_step')
@section('content')

<section class="MainHeading">
	<div class="headingBox">
		<h1>TrashScanApp Thank You</h1>
	</div>
</section>
<!-- ------------------------------------------------------ -->
<section class="textwithform">
	<div class="container">
			<div class="row">
				 
			<div class="col-lg-12 col-md-12">
				<div class="sideForm">
				<h3>
				Your trial account is now active. Please proceed to <a href="{{url('/login')}}">login</a>.
				</h3>
				 
				</div>
			</div>
		</div>
	</div>
</section>


@endsection