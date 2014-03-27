#Composite Config

Our composite config package enhances `illuminate/config` to allow configuration items to be placed within a database whilst cascading back to the filesystem.

This is super useful for building user interfaces that facilitate editing configuration for an app. Because it does not change the API for retrieving configuration items, it degrades gracefully to the filesystem if not present and requires zero changes to the places which use the configuration items.

Part of the Cartalyst Arsenal & licensed [OSI BSD 3](license.md). Code well, rock on.

##Package Story

History and future capabilities.


Versioning
----------

We version under the [Semantic Versioning](http://semver.org/) guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major (and resets the minor and patch)
* New additions without breaking backward compatibility bumps the minor (and resets the patch)
* Bug fixes and misc changes bumps the patch

Support
--------

Have a bug? Please create an issue here on GitHub that conforms with [necolas's guidelines](https://github.com/necolas/issue-guidelines).

https://github.com/cartalyst/composite-config/issues

Follow us on Twitter, [@cartalyst](http://twitter.com/cartalyst).

Join us for a chat on IRC.

Server: irc.freenode.net
Channel: #cartalyst
