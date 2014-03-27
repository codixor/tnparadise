## Inheritance

Our themes package has a feature called inheritance. When you are [configuring]({url}/installation/configuration) your theme, you may specify a parent theme.

When you extend a theme, you only have to copy the files you wish to replace. So, if you only want to override one view and two assets, just copy them across to the same path within your child theme.

Theme inheritance works infinitely as well (in addition to a fallback theme). A practical example of this may include:

1. A "base" theme for your online store.
2. A "holiday" theme, which alters a couple of colors and layouts, who's parent is "base".
3. A "christmas" theme, which changes the header color, who's parent is "holiday".

As you can see, you can easily add new themes which inherit from others, without having to change the original theme.
