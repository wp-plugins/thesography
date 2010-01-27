<?php
/*
Plugin Name: Thesography
Plugin URI: http://www.kristarella.com/thesography
Description: Displays EXIF data for images uploaded with WordPress and enables import of latitude and longitude EXIF to the database upon image upload. <strong>Please visit the <a href="options-general.php?page=thesography">Thesography Options</a> before use.</strong>
Author: kristarella
Version: 1.0.2
Author URI: http://www.kristarella.com
*/

/*
	Thesography is Copyright 2009 Kristen Symonds
	
	Thesography is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	any later version.

	Thesography is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// === LANGUAGE FILE === //
$plugin_url = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) . '/';
load_plugin_textdomain('thesography', "$plugin_url");

// === EDIT POST OPTIONS === //
/*
	*
	* thesography_add_custom_box() registers the meta box for the post edit page
	*
*/
function thesography_add_custom_box() {
if( function_exists('add_meta_box'))
	add_meta_box('thesography_add_meta', __( 'Add EXIF to post', 'thesography' ), 'thesography_edit_post_exif', 'post', 'side', 'low');
}
add_action('admin_menu', 'thesography_add_custom_box');

/*
	*
	* thesography_edit_post_exif() creates the content of the post edit meta box
	*
*/
function thesography_edit_post_exif() {
global $post_ID;

echo'<input type="hidden" name="thesography_noncename" id="thesography_noncename" value="' . wp_create_nonce('thesography_nonce') . '" />';

// fetching Thesography options
$get_options = maybe_unserialize(get_option('thesography_options'));
if ($get_options) {
	foreach ($get_options as $key => $value) {
		$get_options[$key] = stripslashes($value);
	}
	extract($get_options);
} else {
	echo '<strong>' . __('Please visit the <a href="options-general.php?page=thesography">Thesography Options</a> before use.', 'thesography') . '</strong>';
}

if (get_post_meta($post_ID, '_use_exif')) {
	$set_exif = get_post_meta($post_ID, '_use_exif', true);
} else {
	$set_exif = $exif_fields;
}
?>

<div style="padding:0.5em 0.9em;">
<p><?php _e('If there is a photo attached to this post, the following details may be added to the end of the post.', 'thesography'); ?></p>
	<?php exif_checklist($set_exif); ?>
</div>

<?php
}


/*
	*
	* thesography_save_postdata() saves the meta box options as a custom field called _use_exif
	*
*/
function thesography_save_postdata($post_id) {

// fetching Thesography options
$get_options = maybe_unserialize(get_option('thesography_options'));
if ($get_options) {
	foreach ($get_options as $key => $value) {
		$get_options[$key] = stripslashes($value);
	}
	extract($get_options);
}
else {
	echo '<strong>' . __('Please visit the <a href="options-general.php?page=thesography">Thesography Options</a> before use.', 'thesography') . '</strong>';
}

// verify this came from our screen and with proper authorization,
// because save_post can be triggered at other times
if (!wp_verify_nonce($_POST['thesography_noncename'], 'thesography_nonce' ))
	return $post_id;
if (!current_user_can( 'edit_post', $post_id ))
	return $post_id;
// OK, we're authenticated

if (isset($_POST['exif_fields']))
	$use_exif = implode(',',$_POST['exif_fields']);
elseif (!(isset($_POST['exif_fields'])) && $exif_fields)
	$use_exif = 'none';
else
	$use_exif = '';

$current_data = get_post_meta($post_id, '_use_exif', true);

	if ($current_data) {
		if ($use_exif == '')
			delete_post_meta($post_id, '_use_exif');
		if ($use_exif == 'none')
			update_post_meta($post_id, '_use_exif', $use_exif);
		elseif ($use_exif != $current_data)
			update_post_meta($post_id, '_use_exif', $use_exif);
	}
	elseif ($use_exif != '')
		add_post_meta($post_id, '_use_exif', $use_exif, true);
}
add_action('save_post', 'thesography_save_postdata');

// === ADMIN OPTIONS === //
/*
	*
	* thesography_admin() adds the options page under Settings
	*
*/
function thesography_admin() {
	add_options_page('Thesography Options', 'Thesography Options', 8, 'thesography', 'thesography_options');
}
add_action('admin_menu', 'thesography_admin');

/*
	*
	* thesography_options() renders the options page and save the options
	*
*/
function thesography_options() {
global $wpdb;
$defaults = array(
	'before_block' => '<ul class="exif">',
	'before_item' => '<li>',
	'after_item' => '</li>',
	'after_block' => '</ul>',
	'sep' => ': '
);
add_option('thesography_options', $defaults);

if (isset($_POST['options_saved'])) {
	foreach ($_POST as $key => $value) {
		$thesography_options[$key] = $value;
	}
	if (isset($_POST['exif_fields']))
		$thesography_options['exif_fields'] = implode(',',$_POST['exif_fields']);
update_option('thesography_options', serialize($thesography_options));
echo '<div id="message" class="updated fade"><p>' . __('Options saved!', 'thesography') . '</p></div>';
}


$get_options = maybe_unserialize(get_option('thesography_options'));
foreach ($get_options as $key => $value) {
	$get_options[$key] = stripslashes($value);
}
extract($get_options);
$set_exif = $exif_fields;

?>
	<div class="wrap">
	<h2><?php _e('Thesography Options', 'thesography'); ?></h2>
	<p><?php _e('For instructions and support please visit the <a target="_blank" href="http://www.kristarella.com/thesography">Thesography plugin page</a>.', 'thesography'); ?></p>
		<form method="post" action="" id="thesography_options">
			<input type="hidden" name="options_saved" value="1">
			<h3><?php _e('Default EXIF to display', 'thesography'); ?></h3>
			<p><?php _e("Set these to create default options for every post. This is useful when <strong>most</strong> of your posts will be displaying EXIF for <strong>a single photo</strong>, and if you're not adding EXIF manually via shortcodes or custom functions.", 'thesography'); ?></p>
				<?php exif_checklist($set_exif); ?>
			<h3><?php _e('Your Custom HTML', 'thesography'); ?></h3>
			<p><?php _e('This is the HTML used to display your exif data. IDs and classes can be used for styling.', 'thesography'); ?></p>
				<p><label for="before_block"><?php _e('Before EXIF block', 'thesography'); ?></label>
					<input type="text" id="before_block" name="before_block" value="<?php echo htmlentities($before_block); ?>" class="regular-text code" /></p>
				<p><label for="before_item"><?php _e('Before EXIF item', 'thesography'); ?></label>
					<input type="text" id="before_item" name="before_item" value="<?php echo htmlentities($before_item); ?>" class="regular-text code" /></p>
				<p><label for="after_item"><?php _e('After EXIF item', 'thesography'); ?></label>
					<input type="text" id="after_item" name="after_item" value="<?php echo htmlentities($after_item); ?>" class="regular-text code" /></p>
				<p><label for="after_block"><?php _e('After EXIF block', 'thesography'); ?></label>
					<input type="text" id="after_block" name="after_block" value="<?php echo htmlentities($after_block); ?>" class="regular-text code" /></p>
				<p><label for="sep"><?php _e('Separator for EXIF label', 'thesography'); ?></label>
					<input type="text" id="sep" name="sep" value="<?php echo htmlentities($sep); ?>" class="regular-text code" /></p>
					<p><label for="geo_link"><?php _e('Link GEO EXIF to Google Maps', 'thesography'); ?></label>
					<input type="checkbox" id="geo_link" name="geo_link" value="geo_link"<?php if($geo_link) echo ' checked="checked"'; ?>" class="regular-text code" /></p>
			
			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		</form>
	</div>
<?php
}

/*
	*
	* exif_checklist() renders the checklist for the post edit meta box and options page
	*
*/
function exif_checklist($set_exif) {
?>
	<ul class="checkboxes">
		<li><input id="check1" value="aperture" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'aperture') !== false) echo 'checked="checked"'; ?> />
			<label for="check1"><?php _e('Aperture', 'thesography'); ?></label></li>
		<li><input id="check2" value="credit" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'credit') !== false) echo 'checked="checked"'; ?> />
			<label for="check2"><?php _e('Credit', 'thesography'); ?></label></li>
		<li><input id="check3" value="camera" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'camera') !== false) echo 'checked="checked"'; ?> />
			<label for="check3"><?php _e('Camera', 'thesography'); ?></label></li>
		<li><input id="check4" value="caption" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'caption') !== false) echo 'checked="checked"'; ?> />
			<label for="check4"><?php _e('Caption', 'thesography'); ?></label></li>
		<li><input id="check5" value="time" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'time') !== false) echo 'checked="checked"'; ?> />
			<label for="check5"><?php _e('Creation time', 'thesography'); ?></label></li>
		<li><input id="check6" value="copy" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'copy') !== false) echo 'checked="checked"'; ?> />
			<label for="check6"><?php _e('Copyright', 'thesography'); ?></label></li>
		<li><input id="check7" value="focus" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'focus') !== false) echo 'checked="checked"'; ?> />
			<label for="check7"><?php _e('Focal length', 'thesography'); ?></label></li>
		<li><input id="check8" value="iso" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'iso') !== false) echo 'checked="checked"'; ?> />
			<label for="check8"><?php _e('ISO', 'thesography'); ?></label></li>
		<li><input id="check9" value="location" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'location') !== false) echo 'checked="checked"'; ?> />
			<label for="check9"><?php _e('Location', 'thesography'); ?></label></li>
		<li><input id="check10" value="shutter" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'shutter') !== false) echo 'checked="checked"'; ?> />
			<label for="check10"><?php _e('Shutter speed', 'thesography'); ?></label></li>
		<li><input id="check11" value="title" type="checkbox" name="exif_fields[]" <?php if(strpos($set_exif, 'title') !== false) echo 'checked="checked"'; ?> />
			<label for="check11"><?php _e('Title', 'thesography'); ?></label></li>
	</ul>
<?php
}

/*
	*
	* admin_register_head() adds stylesheet to options page
	*
*/
function admin_register_head() {
	$url = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__),"",plugin_basename(__FILE__)) . 'styles/admin.css';
	echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
}
add_action('admin_head', 'admin_register_head');



// === ADD GEO EXIF TO DATABASE === //
function add_geo_exif($meta,$file,$sourceImageType) {
		$exif = @exif_read_data( $file );
			if (!empty($exif['GPSLatitude']))
				$meta['latitude'] = $exif['GPSLatitude'] ;
			if (!empty($exif['GPSLatitudeRef']))
				$meta['latitude_ref'] = trim( $exif['GPSLatitudeRef'] );
			if (!empty($exif['GPSLongitude']))
				$meta['longitude'] = $exif['GPSLongitude'] ;
			if (!empty($exif['GPSLongitudeRef']))
				$meta['longitude_ref'] = trim( $exif['GPSLongitudeRef'] );
	
	return $meta;
}
add_filter('wp_read_image_metadata', 'add_geo_exif','',3);

// return geo exif in a nice form
function geo_frac2dec($str) {
	@list( $n, $d ) = explode( '/', $str );
	if ( !empty($d) )
		return $n / $d;
	return $str;
}

function geo_pretty_fracs2dec($fracs) {
	return	geo_frac2dec($fracs[0]) . '&deg; ' .
			geo_frac2dec($fracs[1]) . '&prime; ' .
			geo_frac2dec($fracs[2]) . '&Prime; ';
}

function geo_single_fracs2dec($fracs) {
	return	geo_frac2dec($fracs[0]) +
			geo_frac2dec($fracs[1]) / 60 +
			geo_frac2dec($fracs[2]) / 3600;
}

/*
	*
	* display_exif() returns the EXIF data in the format specified on the options page
	*
*/
function display_exif($option = 'all',$imgID = null) {
global $post;
// fetching Thesography options
$get_options = maybe_unserialize(get_option('thesography_options'));
if ($get_options) {
	foreach ($get_options as $key => $value) {
		$get_options[$key] = stripslashes($value);
	}
	extract($get_options);
} elseif (is_admin() || current_user_can('level_10')) {
	echo '<strong>' . __('Please visit the <a href="options-general.php?page=thesography">Thesography Options</a> before use.', 'thesography') . '</strong>';
}

	if (is_null($imgID)) {
		$images = get_children(array(
			'post_parent' => $post->ID,
			'post_type' => 'attachment',
			'numberposts' => 1,
			'post_mime_type' => 'image',
			'orderby' => 'ID',
			'order' => 'ASC'
			));
		if ($images) {
			foreach ($images as $image) {
				$imgID = $image->ID;
			}
		}
	}

	$imgmeta = wp_get_attachment_metadata($imgID);

if ($imgmeta) :

if ($imgmeta['image_meta']['latitude'])
	$latitude = $imgmeta['image_meta']['latitude'];
if ($imgmeta['image_meta']['longitude'])
	$longitude = $imgmeta['image_meta']['longitude'];
if ($imgmeta['image_meta']['latitude_ref'])
	$lat_ref = $imgmeta['image_meta']['latitude_ref'];
if ($imgmeta['image_meta']['longitude_ref'])
	$lng_ref = $imgmeta['image_meta']['longitude_ref'];
	$lat = geo_single_fracs2dec($latitude);
	$lng = geo_single_fracs2dec($longitude);
	if ($lat_ref == 'S') { $neg_lat = '-'; } else { $neg_lat = ''; }
	if ($lng_ref == 'W') { $neg_lng = '-'; } else { $neg_lng = ''; }
	if ($geo_link) {
		$start_geo_link = '<a href="http://maps.google.com/maps?q=' . $neg_lat . number_format($lat,6) . '+' . $neg_lng . number_format($lng, 6) . '&z=11">';
		$end_geo_link = '</a>';
	}


	$exif_list = $before_block;
	// Aperture
	if ((strpos($option, 'aperture') !== false || $option == 'all') && !empty($imgmeta['image_meta']['aperture']))
		$exif_list .= $before_item . __('Aperture', 'thesography') . $sep . "f/" . $imgmeta['image_meta']['aperture'] . $after_item;
	// Credit
	if ((strpos($option, 'credit') !== false || $option == 'all') && !empty($imgmeta['image_meta']['credit']))
		$exif_list .= $before_item . __('Credit', 'thesography') . $sep . $imgmeta['image_meta']['credit'] . $after_item;
	// Camera
	if ((strpos($option, 'camera') !== false || $option == 'all') && !empty($imgmeta['image_meta']['camera']))
		$exif_list .= $before_item . __('Camera', 'thesography') . $sep . $imgmeta['image_meta']['camera'] . $after_item;
	// Caption
	if ((strpos($option, 'caption') !== false || $option == 'all') && !empty($imgmeta['image_meta']['caption']))
		$exif_list .= $before_item . __('Caption', 'thesography') . $sep . $imgmeta['image_meta']['caption'] . $after_item;
	// Creation time
	if ((strpos($option, 'time') !== false || $option == 'all') && !empty($imgmeta['image_meta']['created_timestamp']))
		$exif_list .= $before_item . __('Taken', 'thesography') . $sep . date('j F, Y',$imgmeta['image_meta']['created_timestamp']) . $after_item;
	// Copyright
	if ((strpos($option, 'copy') !== false || $option == 'all') && !empty($imgmeta['image_meta']['copyright']))
		$exif_list .= $before_item . __('Copyright', 'thesography') . $sep . $imgmeta['image_meta']['copyright'] . $after_item;
	// Focal length
	if ((strpos($option, 'focus') !== false || $option == 'all') && !empty($imgmeta['image_meta']['focal_length']))
		$exif_list .= $before_item . __('Focal length', 'thesography') . $sep . $imgmeta['image_meta']['focal_length'] . " mm" . $after_item;
	// ISO
	if ((strpos($option, 'iso') !== false || $option == 'all') && !empty($imgmeta['image_meta']['iso']))
		$exif_list .= $before_item . __('ISO', 'thesography') . $sep . $imgmeta['image_meta']['iso'] . $after_item;
	// Latitude and Longtitude
	if ((strpos($option, 'location') !== false || $option == 'all') && $latitude != 0 && $longitude != 0)
		$exif_list .= $before_item . __('Location', 'thesography') . $sep . $start_geo_link . geo_pretty_fracs2dec($latitude) . $lat_ref . ' ' . geo_pretty_fracs2dec($longitude) . $lng_ref . $end_geo_link . $after_item;
	// Shutter speed
	if ((strpos($option, 'shutter') !== false || $option == 'all') && !empty($imgmeta['image_meta']['shutter_speed'])) {
		$exif_list .= $before_item . __('Shutter speed', 'thesography') . $sep;
		if ((1 / $imgmeta['image_meta']['shutter_speed']) > 1) {
			$exif_list .= "1/";
			if ((number_format((1 / $imgmeta['image_meta']['shutter_speed']), 1)) == 1.3
			or number_format((1 / $imgmeta['image_meta']['shutter_speed']), 1) == 1.5
			or number_format((1 / $imgmeta['image_meta']['shutter_speed']), 1) == 1.6
			or number_format((1 / $imgmeta['image_meta']['shutter_speed']), 1) == 2.5) {
				$exif_list .= number_format((1 / $imgmeta['image_meta']['shutter_speed']), 1, '.', '') . " s" . $after_item;
			}
			else {
				$exif_list .= number_format((1 / $imgmeta['image_meta']['shutter_speed']), 0, '.', '') . " s" . $after_item;
			}
		}
		else {
			$exif_list .= $imgmeta['image_meta']['shutter_speed']." s" . $after_item;
		}
		}
	// Title
	if ((strpos($option, 'title') !== false || $option == 'all') && !empty($imgmeta['title']['focal_length']))
		$exif_list .= $before_item . __('Title', 'thesography') . $sep . $imgmeta['image_meta']['title'] . $after_item;
	$exif_list .= $after_block;
	
	return $exif_list;
endif;
}


// === ADD EXIF TO POSTS === //
// via shortcode
function exif_shortcode($atts, $content = null) {
	global $post;

	extract(shortcode_atts(array(
		'id' => '',
		'show' => 'all',
	), $atts));

	$images = get_children(array(
		'post_parent' => $post->ID,
		'post_type' => 'attachment',
		'numberposts' => 1,
		'post_mime_type' => 'image',
		'orderby' => 'ID',
		'order' => 'ASC'
		));
	if ($images) {
	foreach ($images as $image) {
		$imageID = $image->ID;
	}
	}
	
	if ($id == '')
		$imgID = $imageID;
	else
		$imgID = $id;


	$display = $show;


	return display_exif($display,$imgID);
}
add_shortcode('exif','exif_shortcode');

// via options in Thesis
function thesography_display_exif() {
	global $post;
	$exif_options = get_post_meta($post->ID, '_use_exif', true);

	if ($exif_options) {
		$images = get_children(array(
			'post_parent' => $post->ID,
			'post_type' => 'attachment',
			'numberposts' => 1,
			'post_mime_type' => 'image',
			'orderby' => 'ID',
			'order' => 'ASC'
			));
		if ($images) {
			foreach ($images as $image) {
				$imgID = $image->ID;
				echo display_exif($exif_options,$imgID);
			}
		}
	}
}
add_action('thesis_hook_after_post','thesography_display_exif',1);
// via options in Thesis to RSS feed
function thesography_display_exif_feed($content) {
global $thesis;
global $post;
$exif_options = get_post_meta($post->ID, '_use_exif', true);
	if (is_feed() && $thesis && $exif_options)
			return $content . display_exif();
	else
		return $content;
}
add_filter('the_content', 'thesography_display_exif_feed');