=== Simple Wordpress Gallery ===
Contributors: Aaron Harun, Shaneandpeter.com
Tags: post, images, gallery, lightbox
Requires at least: 3.0
Tested up to: 3.0.2
Stable tag: 1.1

Overrides the standard WordPress gallery with a film-strip style one.

== Description ==

SP Gallery overrides existing [gallery] shortcodes in WordPress posts and pages and adds a few much-needed options to the existing WordPress gallery functions.

Usage is simple:

1. Upload images as you normally would to any WordPress post.
2. Optionally, you may use the new "show in gallery" option to hide and display individual images.
3. Add the [gallery] shortcode to your post. 

It's really that easy. Simple WordPress Gallery also has some options built-in to customize the way your gallery is displayed.

1. On the Simple WordPress Gallery admin page, you may customize the image sizes.
2. Use [gallery id="some-post-id"] to display a gallery from a different post.
3. Use [gallery order="DESC"] to reverse the order images are displayed in.
4. Or use orderby to change which fields the images are arranged by.
5. You can set custom dimensions on a single gallery by using: [gallery width=100 height=200]
6. Want the images to change autmotically? Use: [gallery timeout=1000] (Set the time in milliseconds. 1000 = 1 second.)
7. Slow down the transition with [gallery speed=6000] (Set the time in milliseconds. 1000 = 1 second.)
8. To hide the controls so the gallery acts as a "slider" use [gallery mode=slider]. (You'll need to set a timeout since there will be no way to change the images.)
9. You can include or exclude images by passing IDs of attachments you want in the gallery: [gallery include=2,3,4,5] or [gallery exclude=12,13,14]
10. Want to open full-sized images in a lightbox? Use [gallery lightox=yes].
11. Gallery can be selectively enabled and disabled by using [gallery default=yes] (If you have "use default" selected in the admin panel, you can use "default=no" to enable Simple WordPress Gallery)

== Installation ==
1. Download the zip file, unzip it and upload to your wp-content/plugins folder
2. Activate.
3. Existing galleries will automatically be converted by this plugin.

== ChangeLog ==

Version 1.1

1. Added lightbox support.
2. Automatically scroll to next images with an optional "chrome-less" slider mode.
3. Lazy loading. Only the first 10 images are preloaded speeding up the loading of your page.
