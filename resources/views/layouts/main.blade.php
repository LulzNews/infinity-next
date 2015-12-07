@extends('layouts.static')

@section('js')
	@yield('required-js')
	
	<script type="text/javascript" id="js-app-data">
		document.getElementsByTagName('html')[0].class = "js";
		
		window.app = {
			'lang'     : {!! json_encode( Lang::parseKey('board') ) !!},
			
			'merchant' : "{{ env('CASHIER_SERVICE') }}",
			
		@if (env('APP_DEBUG'))
			'debug'      : true,
			
			@if (env('STRIPE_LIVE_PUBLIC') && env('CASHIER_SERVICE') == "stripe")
			'stripe_key' : "{!! env('STRIPE_TEST_PUBLIC', '') !!}",
			@endif
			
			@if (env('CASHIER_SERVICE') == "braintree" && isset($BraintreeClientKey))
			'braintree_key' : "{!! $BraintreeClientKey !!}",
			@endif
			
		@else
			'debug'      : false,
			
			@if (env('STRIPE_LIVE_PUBLIC') && env('CASHIER_SERVICE') == "stripe")
			'stripe_key' : "{!! env('STRIPE_LIVE_PUBLIC', '') !!}",
			@endif
			
			@if (env('CASHIER_SERVICE') == "braintree" && isset($BraintreeClientKey))
			'braintree_key' : "{!! $BraintreeClientKey !!}",
			@endif
		@endif
			
			'favicon'    : {
				'normal' : "{{ asset('static/img/assets/Favicon_Vivian.ico') }}",
				'alert'  : "{{ asset('static/img/assets/Favicon_Vivian_new.ico') }}"
			},
			
			'title'      : "@yield('title', 'Infinity Next')",
			
			'url'        : "{!! env('APP_URL', 'false') !!}",
			
			@yield('app-js')
			
			'settings'   : {!! $app['settings']->getJson() !!},
			
			'version'    : 0
		};
	</script>
	
	{!! Minify::javascriptDir('/static/vendor/',     ['data-no-instant'])->withFullUrl() !!}
	{!! Minify::javascriptDir('/static/js/plugins/', ['data-no-instant'])->withFullUrl() !!}
	{!! Minify::javascriptDir('/static/js/app/',     ['data-no-instant'])->withFullUrl() !!}
	
	@parent
@stop

@section('footer')
<footer>
	@yield('footer-inner')
	
	@section('nav-footer')
		@include('nav.boardlist')
	@show
	
	<section id="footnotes">
		<!-- Infinity Next is licensed under AGPL 3.0 and any modifications to this software must link to its source code which can be downloaded in a traditional format, such as a repository. -->
		<div class="copyright"><a class="agpl-compliance" href="https://github.com/infinity-next/infinity-next">Infinity Next</a> &copy; <a class="agpl-compliance" href="https://infinitydev.org">Infinity Next Development Group</a> 2015</div>
	</section>
	
	<div id="bottom"></div>
</footer>
@stop