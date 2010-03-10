=== Plugin Name ===
Contributors: kristarella
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7439094
Tags: exif, iptc, photos, photographs, photoblog
Requires at least: 2.7
Tested up to: 2.9.2
Stable tag: 1.0.3

Thesography displays EXIF data for images and enables import of latitude and longitude EXIF to the database.

== Description ==

Thesography displays EXIF data for images uploaded with WordPress. It utilises WordPress’ own feature of storing EXIF fields in the database, and also enables import of latitude and longitude EXIF to the database upon image upload.

The purpose of this plugin is to make dislaying EXIF data as convenient as possible, while using as much of WordPress’ native image handling as possible.

EXIF can be displayed via a shortcode, via a function in your theme files and it can be inserted automatically into the Thesis theme from DIYthemes.

== Installation ==

1. Upload the thesography folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the Thesography Options page under the WordPress Settings menu.
1. See the [Thesography plugin page](http://www.kristarella.com/thesography/) for instructions on EXIF display.

== Frequently Asked Questions ==

= Can I use Thesography to display EXIF for my Flickr images? =

No. Thesography uses data imported to the WordPress database upon image upload. Only images uploaded with WordPress will have their EXIF data available to Thesography.

= Can automatic insertion be used with themes other than Thesis? =

Not at this time, but if you have a theme that you think should be integrated please leave a suggestion in the [Thesography plugin page comments](http://www.kristarella.com/thesography/#comments).

== Screenshots ==

1. Options Page allows you to set default post options for EXIF display and customise the HTML output of EXIF for styling.
2. Each post can have its own EXIF items displayed.

== Changelog ==

= 1.0.3 =
* Fixed language file location issue
* Added option to turn off autoinsert in Thesis

= 1.0.2 =
* Fixed bug for new installs on WP2.9.1
* Added languages directory with localisation files

= 1.0.1 =
* Fixed errors regarding foreach and string arguments when no image is attached.
* EXIF added automatically to syndication feeds when Thesis automatic insertion is used.

= 1.0 =
* Displays message to visit options page if trying to write a post before options have been saved, to avoid PHP errors.

= 1.0b =
* Fixed detection of image when no image ID is given