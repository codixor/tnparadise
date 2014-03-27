## Theme Structure

Each theme has two different main directories:

1. **Views** - contains the views for the theme.
2. **Assets** - contains the assets for the theme.

A theme may have global views and assets, as well as **sectioned** views an assets. The sections which themes have are:

2. **Packages** - contains views and assets for a package.
3. **Namespaces** - contains views and assets for a "namespace", which is a predefined location for views (such as pagination views). We allow theming of namespaced views.

How would this structure work? Let's say we have a theme with views for a package named `foo/bar`. Additionally, we are also theming `pagination` views. Our theme could look like:

	assets
	|  css
	|  |  style.css
	|  less
	|  |  bootstrap.less
	|  js
	|  |  jquery.js
	namespaces
	|  pagination
	|  |  assets
	|  |  |  paginator.css
	|  |  views
	|  |  |  paginator.blade.php
	packages
	|  foo
	|  |  bar
	|  |  |  assets
	|  |  |  |  js
	|  |  |  |  |  bar.js
	|  |  |  views
	|  |  |  |  partials
	|  |  |  |  |  table.blade.php
	views
	|  home.blade.php
	|  signup.php

As you can see, each theme has two directories, `assets` and `views`. It also has `packages` and `namespaces`, each of which has the corresponding package or namespace and then their own `assets` and `views` directories.
