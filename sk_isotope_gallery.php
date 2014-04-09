<?php
/*
Plugin Name: Isotope Gallery
Plugin URI: http://riaextended.com/isotope-gallery/
Description: Isotope Gallery is a WordPress Plugin that shows your images in an interactive way
Author: RiaExtended
Version: 1.3.0
Author URI: http://riaextended.com
*/

define('IISOTOPE_TEMPPATH', plugins_url('', __FILE__));
define('IISOTOPE_CLASS_PATH', plugin_dir_path(__FILE__));
define('IISOTOPE_TEXTDOMAIN', 'isotopegallery');
load_plugin_textdomain(IISOTOPE_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );


require_once(IISOTOPE_CLASS_PATH.'/com/riaextended/php/plugin_core.php');


$isotopegallery_app = new SKIsotopeGallery();
$isotopegallery_app->start();

//register activation handler
register_activation_hook(__FILE__, 'activate_isotope_gallery_plugin' );
function activate_isotope_gallery_plugin()
{	
	if(version_compare(get_bloginfo('version'), '3.5', '<' )){
		deactivate_plugins(basename( __FILE__ ));
	}
}

?>
