# Instructions

Install the theme class into your app normally.

Add the following to your bundles.php file.

	'theme' => array('auto' => true)

In start.php change the response in

	Event::listen(View::loader, function($bundle, $view)
	{
		return View::file($bundle, $view, Bundle::path($bundle).'views');
	});

to

	return Theme::file($bundle, $view);
