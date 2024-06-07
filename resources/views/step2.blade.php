@extends('layouts.package_step')
@section('content')
<style>
.ytb{
	margin-bottom:10px;
	border: 1px solid #ccc;
}
.ytb_title{
	font-size:12px;
	padding:8px;
	font-weight:bold
}

</style>
<section class="MainHeading">
	<div class="headingBox">
		<h1>Explore Trash Scan</h1>
	</div>
</section>
<!-- ------------------------------------------------------ -->
<section class="textwithform">
	<div class="container">
			<div class="row">
			<?php
			if(isset($_GET['youtube'])){
				if($_GET['youtube']!=''){
					$vidid = $_GET['youtube'];
				} else {
					$vidid = 'OZhD4olTfpc';
				}
			} else {
				$vidid = 'OZhD4olTfpc';
			}
			?>
				
				<div class="col-lg-8 col-md-4">
					<iframe width="100%" height="420" src="https://www.youtube.com/embed/<?php echo $vidid;?>?autoplay=1&rel=0">
					</iframe>
					
						<div class="col-md-12">
							<div class="row">
								<div class="col-md-3 border" style="padding:0px;margin:10px;"><a href="{{url('free-trial?youtube=OZhD4olTfpc')}}"><img src="http://i3.ytimg.com/vi/OZhD4olTfpc/maxresdefault.jpg" class="img img-responsive"> <div class="ytb_title">Trash Scan On-demand Demo</div></a> </div>
								<div class="col-md-3 border" style="padding:0px;margin:10px;"><a href="{{url('free-trial?youtube=XpVLogCmglk')}}"><img src="http://i3.ytimg.com/vi/XpVLogCmglk/maxresdefault.jpg" class="img img-responsive"> <div class="ytb_title">Trash Scan Valet Trash Cloud Solution Overview</div></a></div>
								<div class="col-md-3 border" style="padding:0px;margin:10px;"><a href="{{url('free-trial?youtube=WSVvluwWVxs')}}"><img src="http://i3.ytimg.com/vi/WSVvluwWVxs/maxresdefault.jpg" class="img img-responsive"> <div class="ytb_title">Monitor Your Valet Trash Operations With Ease</div></a></div>
								<div class="col-md-3 border" style="padding:0px;margin:10px;"><a href="{{url('free-trial?youtube=QqbfjoBp100')}}"><img src="http://i3.ytimg.com/vi/QqbfjoBp100/maxresdefault.jpg" class="img img-responsive"> <div class="ytb_title">Valet Trash Violaton Management Smiplified</div></a></div>
								<div class="col-md-3 border" style="padding:0px;margin:10px;"><a href="{{url('free-trial?youtube=QqbfjoBp100')}}"><img src="http://i3.ytimg.com/vi/7C4-HNiJ26c/maxresdefault.jpg" class="img img-responsive"> <div class="ytb_title">Valet Trash Service Insights for Property Managers</div></a></div>
							</div>
						</div>
						
					
				
					
				
				</div>
				
				
				
				<!-- ------------------------------------------------------ -->
			<div class="col-lg-4 col-md-6">
				<div class="sideSteps">
				<h3>Next Steps</h3>
				<ul>
				<li>View what our customers have to say.</li>
				<li><a href="https://trashscanapp.com/customer-testimonials/" target="_blank">click to view »</a></li>
				</ul>
					
					<ul>
				<li>See all your options, and get a free trial.</li>
				<li><a href="{{url('plan-pricing')}}">editions & pricing »</a></li>
				</ul>
				</div>
			</div>
		</div>
	</div>
</section>


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