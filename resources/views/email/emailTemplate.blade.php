@component('mail::layout')
{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<img src="{{url('assets/images/email_logo.png')}}" width="90" height="90"> 
<div>Trash Scan</div>
@endcomponent
@endslot 
{{-- Body --}}
@isset($detail) 
{!! $detail !!}
@endisset
@isset($url)
Hello,<br/><br/>
@isset($body)
{{$body}}
@else
We are informing you that a new violation has been reported against your apartment/home.  Please view the following link for details of the violation.
@endisset
@component('mail::button', ['url' => $url,'color' => 'green'])
Click here to see violation detail
@endcomponent
Thanks
@endisset
{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset
{{-- Footer --}}
@slot('footer')
@component('mail::footer')
© {{ date('Y') }} {{ config('app.name') }}.
@endcomponent
@endslot
@endcomponent