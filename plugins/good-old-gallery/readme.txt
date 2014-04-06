=== Good Old Gallery ===
Contributors: linuslundahl
Donate link: https://flattr.com/thing/119963/Good-Old-Gallery
Tags: simple, image, gallery, slideshow, jQuery, Flexslider, slide, sliding, fade, fading, multiple, widget
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 2.1.2

Good Old Gallery helps you use galleries on multiple pages and posts, it also uses jQuery plugins for sliding and fading transitions.

== Description ==

Good Old Gallery is a WordPress plugin that helps you upload image galleries that can be used on more than one page/post, it utilizes the built in gallery functionality in WP. Other features include built in Flexslider and jQuery Cycle support and Widgets.

= Main features =

* Uses built in WP gallery functionality.
* [Flexslider](http://www.woothemes.com/flexslider/) or [jQuery Cycle](http://jquery.malsup.com/cycle/).
* Shortcode generator.
* Widgets.
* Stylesheet theme support, per gallery.
* Create your own themes.
* Instant on, no need for coding.
* Plus much more...

== Installation ==

1. Upload `good-old-gallery` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You now have a new Galleries section underneath Media in the admin menu.
4. Go to *Galleries -> Settings* to setup the basic settings.

= Uploading =

1. Click on *Add New* in the Galleries menu.
2. Give your gallery an administrative title.
3. Click on *Upload images*.
4. Upload your images and add Title, Description and Link if needed.
5. Click on *Save all changes*, close the pop-up.
6. Click on *Publish* to enable your gallery.

= Shortcodes =

To use your new gallery in a page/post you use the `[good-old-gallery]` shortcode, use the generator to build one.

1. Go to a page or a post.
2. Click on the *Insert Good Old Gallery* icon next to *Upload/Insert*.
3. Generate your shortcode and click *Insert into post*.

= Themes =

You can make your own themes, just create a `gog-themes` directory in either `wp-content` or your active themes directory, and the plugin will automatically find any themes located there.

Creating a theme is rather simple, just start out with one of the themes found in the `themes` catalog in `good-old-gallery`.

The structure of a theme is:

`gog-themes 
|- my-theme/my-theme/*.* (any image resources used in theme-name.css)
|- my-theme/my-theme.css (this is the only required file)  
|- my-theme/my-theme.png (preview in with size 300x150 px, must be a png)`

Fill in the file headers in the css, only *Style Name* and *Class* are required, but the more you fill in the better.

Now add some css to style your Good Old Galleries.

When using an image in your css you should use `url(theme-name/image-name.png)` without quotes. This is to allow the cached css file to add absolute paths to your images.

If you enable all themes on the settings page and build the css cache, you can use `[good-old-gallery theme="theme-class"]` to use per gallery themes.

= Widgets =

You are also given a new widget called *Good Old Gallery*, use it in regions where a selected gallery always should be shown.

== Frequently Asked Questions ==

The FAQ is currently empty.

== Screenshots ==

1. Galleries listing page
2. Edit gallery page
3. Default settings page
4. Gallery shortcode generator
5. Widget settings

== Changelog ==

= 2.1.2 =
* Bugfix for WP 3.5.

= 2.1 =
* Rewritten and optimized.
* Themes are now stored in sub-folder of the `gog-themes` directory.

= 2.0.3 =
* Trying to fix issues with version 2.x for some people.
* Fix theme selection in the shortcode generator.
* Fix hiding Description.
* Code optimizing.

= 2.0.1 =
* Fixes issues when no slider plugin is selected.
* Fixes hiding Title and Description.

= 2.0 =
* Added Flexslider support too.
* Uses the Settings API.
* Better shortcode genreator.
* Attachments overview and quick delete attachment on gallery posts.
* Works with WordPress 3.3.
* Checks posts and widgets for Good Old Galleries, if none are found no JS or CSS is added.

= 1.13 =
* Theme settings is now on a separate page.

= 1.12 =
* Fixes an issue with "activate all themes".

= 1.11 =
* Added missing cache.php file.
* Remove button on images in galleries is back.

= 1.1 =
* Add your own themes in `wp-content/gog-themes` or `wp-content/themes/[active-theme]/gog-themes`, see [Installation](http://wordpress.org/extend/plugins/good-old-gallery/installation/) for more info.
* Fixed issue with title in widgets.

= 1.0 =
* First version on wordpress.org.

== Upgrade Notice ==

= 2.1 =
Please not that any custom themes you have should now be stored in separate folders, so for example: theme-name.css, theme-name.png should now be in theme-name/theme-name.css and theme-name/file.png inside your `gog-themes` directory.

= 2.0 =
!! WARNING !!  
The plugin now comes with both Flexslider and jQuery Cycle support, if you've been using the plugin for long time it might be best to stick with jQuery Cycle.  
Once the plugin is updated please *re-save the settings page.*

= 1.12 =
Once again you need to *re-save the settings page.*

= 1.1 =
The new theme system allows you to add your own themes.  
*When upgrading you need to re-save the settings on the settings page.*