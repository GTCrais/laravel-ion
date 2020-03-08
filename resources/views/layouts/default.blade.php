<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
		<meta name="description" content=""/>
		<meta name="author" content=""/>
		<meta name="csrf-token" content="{{ csrf_token() }}">

		<title>Ion :: Administration</title>

		<link href="/vendor/ion/css/vendor.css" rel="stylesheet"/>
		<link href="/vendor/ion/css/app.css" rel="stylesheet"/>

		@foreach (Ion::$styles as $key => $path)
			<link rel="stylesheet" href="{{ route('style', $key) }}">
		@endforeach

		{{--<link rel="icon" href="/assets/images/favicon.png" type="image/x-icon">--}}
	</head>

	<body class="bg-theme bg-theme7"
		  data-ionapipath="{{ $ionApiPath }}"
		  data-ionwebpath="{{ $ionWebPath }}"
	>

		<div id="vue-wrapper">

			<!-- start loader -->
			<div id="pageloader-overlay" class="visible incoming">
				<div class="loader-wrapper-outer">
					<div class="loader-wrapper-inner" >
						<div class="loader"></div>
					</div>
				</div>
			</div>
			<!-- end loader -->

			<app></app>

		</div>

		<script src="/vendor/ion/js/manifest.js?v={{ config('assetversioning.ion.js') }}"></script>
		<script src="/vendor/ion/js/vendor.js?v={{ config('assetversioning.ion.jsVendors') }}"></script>
		<script src="/vendor/ion/js/app.js?v={{ config('assetversioning.ion.js') }}"></script>

		<script>
			window.Ion = new InitIon();
		</script>

		@foreach (Ion::$remoteScripts as $url)
			<script src="{!! $url !!}"></script>
		@endforeach

		@foreach (Ion::$localScripts as $key => $path)
			<script src="{{ route('script', $key) }}"></script>
		@endforeach

		<script>
			Ion.launch();
		</script>

	</body>
</html>
