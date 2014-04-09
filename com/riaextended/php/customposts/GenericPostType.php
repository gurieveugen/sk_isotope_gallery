<?php
/**
 * generic CPT
 */
require_once(IISOTOPE_CLASS_PATH.'/com/riaextended/php/customposts/impl/iGenericPostType.php');
class RXGenericPostType implements IGGenericPostTypeRX{
	
	protected $settings;
	protected $cptHelper;
		
	//create custom post
	public function create($cptHelper, $settings){
		$this->settings = $settings;
		$this->cptHelper = $cptHelper;
		register_post_type($settings['post_type'], $cptHelper->getPostArgs());
	}
	
	//return post settings
	public function getSettings(){
		return $this->settings;
	}
	
	//return post slug
	public function getPostSlug(){
		return $this->settings['post_type'];
	}
	
	//add meta box 
	public function addMetaBox($boxTitle, $box_id='_boxID', $callBack='meta_box_content', $context='normal', $priority='high'){
		add_meta_box($this->settings['post_type'].$box_id, $boxTitle, array($this, $callBack), $this->settings['post_type'], $context, $priority);
	}		
	
	//to be overriden
	public function meta_box_content(){}
	
}

?>