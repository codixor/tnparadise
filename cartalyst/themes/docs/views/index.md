## Views

By default, Themes ships with view support for [illuminate/view](http://github.com/illuminate/view) which is the package that drives Laravel 4 views. Our demos and documenation will use this package's view system.

Once you've set the [active theme]({url}/installation/configuration), view loading is the same as it always was:

	View::make('foo/bar', $data);

What will happen here is:

1. The theme system will look for `views/theme/bar.blade.php` in the active theme and all of it's parents, followed by the fallback theme if it is set.
2. If it can find it, it will render it. If it can't, it will fallback to `illuminate/view` to find it.
3. If nothing finds it, an exception is thrown as normal.

### Namespaces & Packages

---

Loading namespaced and package views is done using the same syntax you are already familiar with for loading namespaced views:

	View::make('vendor/package::view/name', $data);
	View::make('namespace::view/name', $data);

If you're loading a namespace view, the theme system will attempt to find the view in the `namespaces` directory (in this case `namespaces/namespace/views/view/name.blade.php`) in the active theme. The rest is the same as normal view loading above.

If you're loading a package view, the theme system will attempt to find the view in the `packages` directory (in this case `packages/vendor/package/views/view/name.blade.php`) in the active theme and all of it's parents, followed by the fallback theme if it is set. If it cannot be found, an exception will be thrown (as there is no native view support for packages).

Easy, huh?
