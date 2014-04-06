# Good Old Gallery

Version 2.1.2

[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=linuslundahl&url=https://github.com/linuslundahl/good-old-gallery&title=Good Old Gallery&language=&tags=github&category=software)

The version hosted at GitHub is __still in development__ and may contain bugs and unfinished code. __Use at own risk!__

Get the latest stable version from [wordpress.org](http://wordpress.org/extend/plugins/good-old-gallery/).

## Change Log

2.1
--------

* __Rewritten and Optimized__  
I have tried to optimize as much as possible to make version 2.1 more maintainable.
* __Theme folder structure changes__  
Themes are now stored in separate folders. See Theme structure for more information.

2.0
---
* __Flexslider__  
Flexslider is now used as the main slider plugin instead of jQuery Cycle, though Cycle is still supported for backwards compatibility. And more plugins can easily be added.
* __Settings API__  
A lot of work has been put on making the settings page use the built in Settings API.
* __Shortcode generator__  
The shortcode generator has gotten some small fixes.
* __Sorting fields__  
It's now possible to sort the Title, Description and Image output.
* __General bug fixes__


## Theme structure

Custom themes can be stored in two locations. `wp-content/gog-themes` or `wp-content/themes/[your-theme]/gog-themes`.  

The theme structure is:

	../gog-themes/theme-name/theme-name/*.* (any image resources used in theme-name.css)
	../gog-themes/theme-name/theme-name.css (required)
	../gog-themes/theme-name/theme-name.png

When using an image in your css you should use `url(theme-name/image-name.png)` without quotes. This is to allow the cached css file to add absolute paths to your images.

See themes located in `good-old-gallery/themes` for reference on how the theme info in the css file should be formatted.