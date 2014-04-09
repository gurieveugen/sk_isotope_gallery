<?php
require_once(IISOTOPE_CLASS_PATH.'com/riaextended/php/plugin_base.php');
require_once(IISOTOPE_CLASS_PATH.'com/riaextended/php/customposts/utils/CPTHelper.php');
require_once(IISOTOPE_CLASS_PATH.'com/riaextended/php/customposts/GalleryCPT.php');
require_once(IISOTOPE_CLASS_PATH.'com/riaextended/php/isotopeShortcodes.php');

/**
 * GalleryWrapper
 */
define('IISOTOPE_SLUG', 'sk_isotopegallery');

class SKIsotopeGallery extends iIsotopePluginBase {
	
	//fire up 
	public function start($options='')
	{	
		parent::start();
		add_action( 'wp_ajax_igallery_ajax_req', array($this, 'igallery_ajax_req') );	
	}
	
	//iGallery attachement request
	public function igallery_ajax_req(){
		$nonce = $_POST['i_nonce'];
		if ( ! wp_verify_nonce( $nonce, 'igallery-nonce-default' ) ){
			die( 'Busted!');	
		}		
		
		$at_id = $_POST['attachementID'];		
		$post_thumb = wp_get_attachment_thumb_url($at_id);
		if(!$post_thumb){
			$post_thumb = 'http://placehold.it/150x150';
		}
		
		$response = json_encode( array( 'thumb_url' => $post_thumb));	 
		header( "Content-Type: application/json");
		echo $response;
		exit;		
	}	
	
	//init handler - override 
	public function initializeHandler(){
		parent::initializeHandler();	
		$this->addGalleryCPT();		
	}
	
	private $iGCPT;
	/*
	 * create youtube CPT
	 */
	private function addGalleryCPT(){
		$settings = array('post_type' => IISOTOPE_SLUG, 'name' => __('IsotopeGallery', IISOTOPE_TEXTDOMAIN), 'menu_icon' => IISOTOPE_TEMPPATH.'/com/riaextended/images/icons/camera-black.png',
		'singular_name' => __('IsotopeGallery', IISOTOPE_TEXTDOMAIN), 'rewrite' => 'isotopegallery', 'add_new' => __('Add new', IISOTOPE_TEXTDOMAIN),
		'edit_item' => __('Edit', IISOTOPE_TEXTDOMAIN), 'new_item' => __('New', IISOTOPE_TEXTDOMAIN), 'view_item' => __('View item', IISOTOPE_TEXTDOMAIN), 'search_items' => __('Search items', IISOTOPE_TEXTDOMAIN),
		'not_found' => __('No item found', IISOTOPE_TEXTDOMAIN), 'not_found_in_trash' => __('Item not found in trash', IISOTOPE_TEXTDOMAIN), 
		'supports' => array('title'));
		
		$cptHelper = new IsotopegalleryCPTHelper($settings);
		$this->iGCPT = new isotopeGalleryCPT();
		$this->iGCPT->create($cptHelper, $settings);
		
	}
	
	//admin init handler - override 
	public function adminInitHandler(){
		//add meta boxes pages
		$this->iGCPT->addMetaBox(__('Gallery\'s groups', IISOTOPE_TEXTDOMAIN), 'groups_container_id', 'groups_metabox');
		$this->iGCPT->addMetaBox(__('Gallery properties', IISOTOPE_TEXTDOMAIN), 'i_properties_id', 'properties_metabox', 'side', 'default');			
	}

	
	
	//admin enqueue scripts handler - override 
	public function adminEnqueueScriptsHandler(){		
		$screenID = get_current_screen()->id;
		if($screenID==IISOTOPE_SLUG){
			
			parent::adminEnqueueScriptsHandler();
			/*================ENQUEUE===============*/			
			
			//$this->enque_common();			
			
			//ui
			wp_register_style('custom_ui_css', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/ui/css/ui.css');
			wp_enqueue_style('custom_ui_css');	
			wp_register_script('custom_ui', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/ui/js/ui.js');
			wp_enqueue_script('custom_ui');						
						
			
			//jqueryui		
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_script('jquery-ui-widget');
			wp_enqueue_script('jquery-ui-selectable');
			wp_enqueue_script('jquery-ui-button');	
			wp_enqueue_script('jquery-ui-mouse');
			wp_enqueue_script('jquery-ui-spinner');
			wp_enqueue_script('jquery-ui-accordion');
			wp_enqueue_script('jquery-ui-dialog');
			
			//utils
			wp_enqueue_script('underscore');
			wp_register_script('sk_utils', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/utils/Utils.js', array('jquery'));	
			wp_enqueue_script('sk_utils');						
					 		 						 	 						
						 
			//jqueryui theme
			wp_register_style('jqueryui-style', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/jqueryui/css/ui-lightness/jquery-ui-1.10.1.custom.min.css');
			wp_enqueue_style('jqueryui-style');			

			

			//enqueue main
			wp_register_script('igallery_main', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/main.js', array('jquery'));	
			wp_enqueue_script('igallery_main');
			
			wp_register_script('igallery_generics', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/generics/generics.js', array('jquery'));	
			wp_enqueue_script('igallery_generics');
			
			//events			
			wp_register_script('event_bus', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/events/eventbus/EventBus.js', array('jquery'));
			wp_enqueue_script('event_bus');
			wp_register_script('generic_events', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/events/genericevents.js', array('jquery'));	
			wp_enqueue_script('generic_events');																
			
			//media																	
			wp_enqueue_media();	
			
			//tween
			wp_register_script('sk_tweenlight', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/tween/TweenLite.min.js', array('jquery'));
			wp_enqueue_script('sk_tweenlight');				
			
			$this->enque_common();
			
			$this->enqueColorPicker();			
			
			//localize script
			$igallery_data = array('AJAX_SERVICE'=>admin_url( 'admin-ajax.php'), 'IGALLERY_NONCE'=> wp_create_nonce('igallery-nonce-default'));				
			wp_localize_script('igallery_main', 'IGALLERY_DTA', $igallery_data);			
		}		
	}

	private function enqueColorPicker(){
			 //color picker style
		     wp_register_style( 'cpicker_style', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/cpicker/css/colorpicker.css');
			 wp_register_style( 'cpicker_layout', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/cpicker/css/layout.css');		 
		     wp_enqueue_style( 'cpicker_style');
			 //wp_enqueue_style( 'cpicker_layout');
			 
			 //color picker script
			 wp_register_script( 'color_picker', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/cpicker/js/colorpicker.js', array('jquery'));
			 wp_register_script( 'color_picker_eye', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/cpicker/js/eye.js', array('jquery'));
			 wp_register_script( 'color_picker_layout', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/cpicker/js/layout.js', array('jquery'));
			 wp_register_script( 'color_picker_utils', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/cpicker/js/utils.js', array('jquery'));
			 wp_enqueue_script('color_picker');
			 wp_enqueue_script('color_picker_eye');	
			 wp_enqueue_script('color_picker_layout');	
			 wp_enqueue_script('color_picker_utils');			 		
	}

	//WP Enqueue scripts handler
	public function WPEnqueueScriptsHandler(){
		parent::WPEnqueueScriptsHandler();		
		$this->enque_common();
		
		wp_register_script('sk_tweenmax', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/tween/TweenMax.min.js', array('jquery'));
		wp_enqueue_script('sk_tweenmax');		
		
		wp_register_style('isotopegallery_css', IISOTOPE_TEMPPATH.'/css/isotopegallery.css');
		wp_enqueue_style('isotopegallery_css');		
		
		wp_register_script('rx_isotope_gallery', IISOTOPE_TEMPPATH.'/js/rx_isotope_gallery.js', array('jquery'));
		wp_enqueue_script('rx_isotope_gallery');
		
		//isotope
		wp_register_script('rx_isotope', IISOTOPE_TEMPPATH.'/js/external/jquery.isotope.min.js', array('jquery'));
		wp_enqueue_script('rx_isotope');		
		
		//localize script
		$gallery_data_front = array('IMAGES_URL'=>IISOTOPE_TEMPPATH.'/images', 'LOOK_AND_FEEL'=>'7b7b7b');				
		wp_localize_script('rx_isotope_gallery', 'GALLERY_DTA_FRONT', $gallery_data_front);																
	}

	private function enque_common(){
			//tween								
			wp_register_script('sk_css_plugin', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/tween/CSSPlugin.min.js', array('jquery'));
			wp_enqueue_script('sk_css_plugin');			
			wp_register_script('sk_tween_ease', IISOTOPE_TEMPPATH.'/com/riaextended/js'.'/tween/easing/EasePack.min.js', array('jquery'));
			wp_enqueue_script('sk_tween_ease');			
	}

	/**
	 * SAVE POST EXTRA DATA
	 */
	 public function savePostHandler(){
		global $post;						
		if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
			return $post_id;
		}
		if(!current_user_can('edit_posts') || !current_user_can('publish_posts')){
			return;
		}		
		//ysave extra data
		if(isset($post) && isset($_POST[$this->iGCPT->getPostSlug().'-data']) && IISOTOPE_SLUG==$_POST['post_type']){			
			update_post_meta($post->ID, $this->iGCPT->getPostSlug().'-data', $_POST[$this->iGCPT->getPostSlug().'-data']);
		}
		if(isset($post) && isset($_POST[$this->iGCPT->getPostSlug().'-thumbs_size']) && IISOTOPE_SLUG==$_POST['post_type']){			
			update_post_meta($post->ID, $this->iGCPT->getPostSlug().'-thumbs_size', $_POST[$this->iGCPT->getPostSlug().'-thumbs_size']);
		}
		if(isset($post) && isset($_POST[$this->iGCPT->getPostSlug().'-extra_data']) && IISOTOPE_SLUG==$_POST['post_type']){
					
			update_post_meta($post->ID, $this->iGCPT->getPostSlug().'-extra_data', $_POST[$this->iGCPT->getPostSlug().'-extra_data']);
		}												
	 }


	/*
	 * register shortcodes 
	 */ 
	public function registerShortcodes(){			
		$shorcodesHelper = new rxIsotopeShortcodes();
		$shorcodesHelper->registerShortcodes();	
	}
			
	
		
}


?>