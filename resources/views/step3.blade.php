@extends('layouts.package_step')
@section('content')


<section class="MainHeading">
	<div class="headingBox">
		<h1>Trash Scan Plans & Pricing <span class="float-right"><a class="tryBtnBg" href="{{url('free-trial')}}" style="font-size:14px;">View Trashscan Demo</a></span></h1>
		
	</div>
	
</section>
<!-- ------------------------------------------------------ -->
 

<!-- ------------------------------------------------------ -->
<section class="TablePart">
	<div class="container">
 
		<div class="tableResp">
			  <table class="table table-striped">
			  
				<tr>
				  <th>Feature</th>
				  <?php
				  $p4 = DB::table('subscriptions')->where('id', 66)->first();
					if($p4->subscription_type=='1'){
						$pt = 'Month';
					} else {
						$pt = 'Year';
					}			  
					?>
				  <th><?php echo $p4->package_offering?> </th>
				  
				  <?php
				  $p5 = DB::table('subscriptions')->where('id', 67)->first();
					if($p5->subscription_type=='1'){
						$pt = 'Month';
					} else {
						$pt = 'Year';
					}			  
					?>
				  <th><?php echo $p5->package_offering?> </th>

				  <?php
				  $p1 = DB::table('subscriptions')->where('id', 4)->first();
					if($p1->subscription_type=='1'){
						$pt = 'Month';
					} else {
						$pt = 'Year';
					}			  
					?>
				  <th><?php echo $p1->package_offering?> </th>
				  
				  <?php
				  $p2 = DB::table('subscriptions')->where('id', 2)->first();
				  if($p2->subscription_type=='1'){
						$pt2 = 'Month';
					} else {
						$pt2 = 'Year';
					}
					?>
				  <th><?php echo $p2->package_offering?></th>
				  <?php
				  $p3 = DB::table('subscriptions')->where('id', 3)->first(); 
				  
				  if($p3->subscription_type=='1'){
						$pt3 = 'Month';
					} else {
						$pt3 = 'Year';
					}
					
					?>
					
				  <th><?php echo $p3->package_offering?></th>
				</tr>
				<tr>
					<td>Digital Valet Trash Properties</td>
					<td>{{$p4->number_of_property}} Properties</td>
					<td>{{$p5->number_of_property}} Properties</td>
					<td>{{$p1->number_of_property}} Properties</td>
					<td>{{$p2->number_of_property}} Properties</td>
					<td>{{$p3->number_of_property}} Properties</td>
				</tr>
				<tr>
					<td>Premium User Licenses</td>
					<td>{{$p4->package_admin}} User</td>
					<td>{{$p5->package_admin}} User</td>
					<td>{{$p1->package_admin}} User</td>
					<td>{{$p2->package_admin}} Users</td>
					<td>{{$p3->package_admin}} Users</td>
				</tr>
				<tr>
					<td>User Licenses</td>
					<td>{{$p4->package_field_collector}} Users</td>
					<td>{{$p5->package_field_collector}} Users</td>
					<td>{{$p1->package_field_collector}} Users</td>
					<td>{{$p2->package_field_collector}} Users</td>
					<td>{{$p3->package_field_collector}} Users</td>
				</tr>				
				<tr>
					<td>Service Route Tracking</td>
					<td>Optional+$25</td>
					<td>Optional+$100</td>
					<td>Optional+$150</td>
					<td>Optional+$200</td>
					<td>Optional+$250</td>
				</tr>
				<tr>
					<td>Property Manager Portal</td>
					<td>Optional</td>
					<td>Optional</td>
					<td>Optional</td>
					<td>Optional</td>
					<td>Optional</td>
				</tr>
				<tr>
					<td>Task Management</td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
				</tr>
				<tr>
					<td>Violation Management</td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
				</tr>
				<tr>
					<td>Bi-Lingual Mobile App Version</td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
				</tr>
				<tr>
					<td>Web Based Training</td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
				</tr>
				<tr>
					<td>Online Product Tutorials Access</td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
					<td><img src="{{url('assets/images/check2.png')}}"></td>
				</tr>
				
				
				<tr>
				  <td>Customer Support 8 am - 10 pm</td>
				  <td><img src="{{url('assets/images/check2.png')}}"></td>
				  <td><img src="{{url('assets/images/check2.png')}}"></td>
				  <td><img src="{{url('assets/images/check2.png')}}"></td>
				  <td><img src="{{url('assets/images/check2.png')}}"></td>
				  <td><img src="{{url('assets/images/check2.png')}}"></td>
				</tr>
				 
				 
				 
				<tr>
					<td>Monthly Total</td>
					<td>$<?php echo $p4->price?>/<?php echo $pt?></td>
					<td>$<?php echo $p5->price?>/<?php echo $pt?></td>
					<td>$<?php echo $p1->price?>/<?php echo $pt?></td>
					<td>$<?php echo $p2->price?>/<?php echo $pt2?></td>
					<td>$<?php echo $p3->price?>/<?php echo $pt3?></td>
				</tr>
				<tr>
					<td></td>
					<td>
						<form method="post" name="register"
							action="{{url('try_for_free')}}" id="packid<?php echo $p4->id;?>">
								{{ csrf_field() }}
								<a href="javascript:void(0)" onclick="submitform(<?php echo $p4->id; ?>)" class="tryBtnBg" style="width:160px;">try for free</a>
					
								<input type="hidden" name="pack_id" value="<?php echo $p4->id; ?>" />
						</form>
					</td>
					<td>
						<form method="post" name="register"
							action="{{url('try_for_free')}}" id="packid<?php echo $p5->id;?>">
								{{ csrf_field() }}
								<a href="javascript:void(0)" onclick="submitform(<?php echo $p5->id; ?>)" class="tryBtnBg" style="width:160px;">try for free</a>
					
								<input type="hidden" name="pack_id" value="<?php echo $p5->id; ?>" />
						</form>
					</td>
					<td>
						<form method="post" name="register"
							action="{{url('try_for_free')}}" id="packid<?php echo $p1->id;?>">
								{{ csrf_field() }}
								<a href="javascript:void(0)" onclick="submitform(<?php echo $p1->id; ?>)" class="tryBtnBg" style="width:160px;">try for free</a>
					
								<input type="hidden" name="pack_id" value="<?php echo $p1->id; ?>" />
						</form>
					</td>
					<td>
						<form method="post" name="register"
							action="{{url('try_for_free')}}" id="packid<?php echo $p2->id;?>">
							{{ csrf_field() }}
							<a href="javascript:void(0)" onclick="submitform(<?php echo $p2->id; ?>)" class="tryBtnBg"  style="width:160px;">try for free</a>
						
							<input type="hidden" name="pack_id" value="<?php echo $p2->id; ?>" />
						</form>
					</td>
					<td>
						<form method="post" name="register"
							action="{{url('try_for_free')}}" id="packid<?php echo $p3->id;?>">
							{{ csrf_field() }}
							<a href="javascript:void(0)" onclick="submitform(<?php echo $p3->id; ?>)" class="tryBtnBg"  style="width:160px;">try for free</a>
						
							<input type="hidden" name="pack_id" value="<?php echo $p3->id; ?>" />
						</form>
					</td>
				</tr>
		    </table>
		</div>
	</div>
<section>
<!-- ------------------------------------------------------ -->

<!-- ------------------------------------------------------ -->
<section class="feature">
<div class="container">
<h2>Explore Features</h2>
<div class="featurBox">
<div class="featurBoxInner">

    <div class="accordion" id="accordionExample">
        <div class="card">
            <div class="card-header" id="headingOne">
                    <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne">
					<b>Operations Management Dashboard</b>
					<i class="fa fa-plus"></i></button>
            </div>
            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                <div class="card-body">
                    <p>Manage your employees, the properties, and daily operations from our online operations management dashboard. Reports available to give you real-time insights into operations and hold your team accountable.</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="headingTwo">
                <h2 class="mb-0">
                    <button type="button" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo">
					<b>Violation Management</b>
					<i class="fa fa-plus"></i> </button>
                </h2>
            </div>
            <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionExample">
                <div class="card-body">
                    <p>Capture and document violations on-the-go. Realtime visibility of new violations. Manage the lifecycle from time of capture to closure.</p>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="headingThree">
                <h2 class="mb-0">
                    <button type="button" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree">
					<b>QR Code Enabled Route Tracking</b>
					<i class="fa fa-plus"></i> </button>                     
                </h2>
            </div>
            <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
                <div class="card-body">
                    <p>Use of highly durable 2D QR code labels as service confirmations for the property backed by our geo-fencing technology for added service integrity.</p>
                </div>
            </div>
        </div>
		
		<div class="card">
            <div class="card-header" id="headingFour">
                <h2 class="mb-0">
                    <button type="button" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseFour">
					<b> Service Notes</b>
					<i class="fa fa-plus"></i> </button>                     
                </h2>
            </div>
            <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionExample">
                <div class="card-body">
                    <p>Easily create service notes from our porter mobile app  for sharing of operations related occurrences or request.</p>
                </div>
            </div>
        </div>
		
		<div class="card">
            <div class="card-header" id="headingFive">
                <h2 class="mb-0">
                    <button type="button" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseFive">
					<b>Property Manager Portal</b>
					<i class="fa fa-plus"></i> </button>                     
                </h2>
            </div>
            <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#accordionExample">
                <div class="card-body">
                    <p>Monitor community waste collection services and manage violations documented by valet trash service providers.</p>
                </div>
            </div>
        </div>
		
		
		
    </div>
</div>
</div>
</div>
</section>


<!-- ------------------------------------------------------ -->
<section class="faqs">
<div class="container">
<h2>FAQs</h2>
			<div class="faqsBox">
<div class="faqsBoxInner">
    <div class="accordion" id="accordionExample_1">
        <div class="card">
            <div class="card-header1" id="fheadingOne">
               <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne_1"><b>Can Trash Scan be used without the QR Code labels?</b>
					<i class="fa fa-plus"></i></button>
            </div>
            <div id="collapseOne_1" class="collapse" aria-labelledby="fheadingOne" data-parent="#accordionExample_1">
                <div class="card-body">
                    <p>Yes, we have multiple features within the mobile app to capture services and violations that do not require implementation of QR Code labels.</p>
                </div>
            </div>
        </div>
		<div class="card">
            <div class="card-header1" id="fheadingTwo">
               <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne_2"><b>Who has to setup the new properties?</b>
					<i class="fa fa-plus"></i></button>
            </div>
            <div id="collapseOne_2" class="collapse" aria-labelledby="fheadingTwo" data-parent="#accordionExample_1">
                <div class="card-body">
                    <p>The Trash Scan team is able to assist with setup of the property upon request.</p>
                </div>
            </div>
        </div>
		<div class="card">
            <div class="card-header1" id="fheadingThree">
               <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne_3"><b>What training is available?</b>
					<i class="fa fa-plus"></i></button>
            </div>
            <div id="collapseOne_3" class="collapse" aria-labelledby="fheadingThree" data-parent="#accordionExample_1">
                <div class="card-body">
                    <p>Trash Scan offers web based training upon request.  There is also an online library of training videos for Trash Scan at our YouTube Channel.</p>
                </div>
            </div>
        </div>
		
		<div class="card">
            <div class="card-header1" id="fheadingFour">
               <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne_4"><b>How long does it take to receive my QR Code Labels after my order?</b>
					<i class="fa fa-plus"></i></button>
            </div>
            <div id="collapseOne_4" class="collapse" aria-labelledby="fheadingFour" data-parent="#accordionExample_1">
                <div class="card-body">
                    <p>Generally the turnaround time for processing orders is no longer than 5 days without shipping time included.</p>
                </div>
            </div>
        </div>
		<div class="card">
            <div class="card-header1" id="fheadingFive">
               <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne_5"><b>How are customers billed for the service?</b>
					<i class="fa fa-plus"></i></button>
            </div>
            <div id="collapseOne_5" class="collapse" aria-labelledby="fheadingFive" data-parent="#accordionExample_1">
                <div class="card-body">
                    <p>Customers are billed monthly. Discount available for those who take advantage of our annual payment option.</p>
                </div>
            </div>
        </div>
		
		<div class="card">
            <div class="card-header1" id="fheadingSix">
               <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne_6"><b>Is the mobile app available on Android, Iphone, or both?</b>
					<i class="fa fa-plus"></i></button>
            </div>
            <div id="collapseOne_6" class="collapse" aria-labelledby="fheadingSix" data-parent="#accordionExample_1">
                <div class="card-body">
                    <p>The mobile app is available on both Android and Iphone devices.</p>
                </div>
            </div>
        </div>
		<div class="card">
            <div class="card-header1" id="fheadingSeven">
               <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne_7"><b>Can packages be customized if my requirements are between the pricing tiers above</b>
					<i class="fa fa-plus"></i></button>
            </div>
            <div id="collapseOne_7" class="collapse" aria-labelledby="fheadingSeven" data-parent="#accordionExample_1">
                <div class="card-body">
                    <p>Yes, we customize packages to fit how many properties and user licenses are required</p>
                </div>
            </div>
        </div>
		<div class="card">
            <div class="card-header1" id="fheadingEight">
               <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne_8"><b>Do you have packages for new startups? (1-3 properties)?</b>
					<i class="fa fa-plus"></i></button>
            </div>
            <div id="collapseOne_8" class="collapse" aria-labelledby="fheadingEight" data-parent="#accordionExample_1">
                <div class="card-body">
                    <p>We offer a monthly package which is priced based on number of units.</p>
                </div>
            </div>
        </div>
		
		
		
		 
    </div>
</div>
</div>
</div>
</section>
<script>
function submitform(id){
	$('#packid'+id).submit();
}
</script>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/5e9cff1869e9320caac55353/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
@endsection