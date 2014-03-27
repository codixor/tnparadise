@extends('cartalyst/sentry-social::template')

@section('content')
<div class="container">
	@if (count($connections) > 0)
		<ul class="thumbnails">
			@foreach ($connections as $slug => $connection)
				<li class="span4">

					<div class="thumbnail">
						<img src="http://placehold.it/400x180">
						<div class="caption">
							<a href="{{ URL::to('oauth/authorize/'.$slug) }}" class="btn">
								Login with {{ $connection['driver'] }}
							</a>
						</div>
					</div>

				</li>
			@endforeach
		</ul>
	@else
		<h3>Snap! <small>No connections yet</small></h3>
		<p>Try configuring a service and reloading this page.</p>
	@endif
</div>
@endsection
