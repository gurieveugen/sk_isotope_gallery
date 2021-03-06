<?php
/**
 * Shortcode
 */
require_once(IISOTOPE_CLASS_PATH.'/com/riaextended/php/libs/rx_resizer.php');


class rxIsotopeShortcodes{

	/**
	 * Temporary fixes
	 * Customer want some new grid layout
	 */
	const G_IMG_WIDTH  = 258;
	const G_IMG_HEIGHT = 228;
	
	public function registerShortcodes(){						
		add_shortcode('isotope_gallery', array($this, 'rx_isotope_gallery'));																										
	}
	
	/* igallery shortcode
	================================================== */	
	public function rx_isotope_gallery($atts, $content = null)
	{
		global $images_layout;
		extract(shortcode_atts(array('id' => ''), $atts));
		$return_val = 'salut';		
		
		//get post
		$gallery_post = get_post($id, OBJECT);
		if(!is_null($gallery_post))
		{
			$gallery_post_meta = get_post_meta($gallery_post->ID, IISOTOPE_SLUG.'-data', false);
			$gallery_thumb_size = get_post_meta($gallery_post->ID, IISOTOPE_SLUG.'-thumbs_size', false);
			$gallery_extra_data = get_post_meta($gallery_post->ID, IISOTOPE_SLUG.'-extra_data', false);
			
			$wdt = 250;	
			if(isset($gallery_thumb_size[0])){
				$wdt = (float)$gallery_thumb_size[0]['width'];										
			}
			$gap01 = 5;
			$lightbox_colors = "6abde9";
			$menu_text_color = "484848";
			$menu_back_color = "6abde9";
			$label_all       = "All";			
			$lightbox        = "no";
													
			if(isset($gallery_extra_data[0]))
			{
				$gap01               = (float)$gallery_extra_data[0]['gap01'];
				$lightbox_colors     = $gallery_extra_data[0]['lightbox_colors'];
				$menu_text_color     = $gallery_extra_data[0]['menu_text_color'];	
				$menu_back_color     = $gallery_extra_data[0]['menu_back_color'];
				$label_all           = $gallery_extra_data[0]['label_all'];														
				$lightbox            = $gallery_extra_data[0]['lightbox'];
				$api_key             = $gallery_extra_data[0]['api_key'];
				$photoset_id             = $gallery_extra_data[0]['photoset_id'];
				$flickr_per_page     = $gallery_extra_data[0]['flickr_per_page'];
				$facebook_user       = $gallery_extra_data[0]['facebook_user'];
				$facebook_group_name = $gallery_extra_data[0]['facebook_group_name'];
				$flickr_group_name   = $gallery_extra_data[0]['flickr_group_name'];
				$layout_name         = $gallery_extra_data[0]['layout_name'];
				$per_page            = $gallery_extra_data[0]['per_page'];
				$per_page_count      = 1;				
			}
			$itemsColor = $menu_back_color;
			$rgba       = $this->html2rgb($itemsColor);	
												
			$isotopeMenuHTML = '<ul class="isotopeMenu" data-selectedcolor="'.$menu_back_color.'" data-lightboxcolor="'.$lightbox_colors.'">';	
			if(!empty($label_all))
			{
				$isotopeMenuHTML .= '<li><a class="eye" style="color: #'.$menu_text_color.';" href="group_all">'.$label_all.'</a></li>';	
			}
			// $isotopeMenuHTML .= '<li><a class="eye" style="color: #'.$menu_text_color.';" href="*">'.$label_all.'</a></li>';	
			
			$isotopeItemsHTML = '';			
			$return_val = '';
			
			if(isset($gallery_post_meta[0])){
				$mainJSONData = (isset($gallery_post_meta[0]['mainJSONData']))?$mainJSONData = $gallery_post_meta[0]['mainJSONData']:$mainJSONData='';				
				$temp_data = json_decode($mainJSONData);

				$groups = $temp_data->groups;
				$count_items = -1;
				//interate groups	
				for ($i=0; $i < sizeof($groups); $i++) {
					$groupName = $groups[$i]->name;
					$id_group = $groups[$i]->idGroup;						
					$isotopeMenuHTML .= '<li><a class="eye" style="color: #'.$menu_text_color.';" href="_group'.$id_group.'">'.$groupName.'</a></li>';					
					$per_page_count = 1;
					// ========================================================
					// Paste Flickr photostream images
					// ========================================================							
					if($groupName == $flickr_group_name)
					{
						$per_page_count                    = 1;
						$options_flickr['api_key']         = $api_key;
						$options_flickr['photoset_id']     = $photoset_id;
						$options_flickr['flickr_per_page'] = intval($flickr_per_page);						
						
						$images_flickr                     = $this->getFlickrPhotoSet($options_flickr);
						if($images_flickr)
						{
							foreach ($images_flickr as $key => $value) 
							{							
								$wdt             = $value->width_m;
								$thumbHeight     = $value->height_m;
								$thumb_url       = $value->url_m;
								$imgFullUrl      = $value->url_o;
								$imageCaption    = $value->title;
								$imageSubCaption = "";
								$all[]			 = array(								
									'full'       => $value->url_o,
									'caption'    => $imageCaption,
									'subcaption' => '');

								if($per_page_count > $per_page) 
								{
									$per_page_count = 1;
								}

								$sizes       = $this->getWidthHeight($layout_name, $images_layout, $per_page_count);
								$wdt         = $sizes[0];
								$thumbHeight = $sizes[1];
								
								$opts        = array(
									"w"    => $wdt,
									"h"    => $thumbHeight,
									"q"    => 100, 
									"crop" => true );

								$thumb_url = get_image_thumb($imgFullUrl, $opts);

								$isotopeItemsHTML .= '<div style="width: '.$wdt.'px; height: '.$thumbHeight.'px; margin: '.$gap01.'px;" class="isotopeItem _group'.$id_group.' itemm'.$per_page_count.'">
									<a class="fancybox-thumb" rel="fancybox-thumb" href="'.$imgFullUrl.'" title="'.$imageCaption.'" data-subtitle="'.$imageSubCaption.'">
										<img class="isotopeThumb" src="'.$thumb_url.'" width="'.$wdt.'" height="'.$thumbHeight.'" />
										<div class="caption">'.$imageCaption.'</div>
										<div class="subcaption">'.$imageSubCaption.'</div>
									</a>																
								</div>';	

								$per_page_count++;	
							}
						}
						
					}

					// ========================================================
					// Paste Facebook photostream images
					// ========================================================	
					if($groupName == $facebook_group_name)
					{
						$per_page_count = 1;
						$json            = file_get_contents("https://graph.facebook.com/".$facebook_user."/photos/uploaded");
						$images_facebook = json_decode($json, true);
						foreach ($images_facebook["data"] as $key => $value) 
						{													
							$wdt             = $value['width'];
							$thumbHeight     = $value['height'];
							$thumb_url       = $value['source'];
							$imgFullUrl      = $value['source'];
							$imageCaption    = "";
							$imageSubCaption = "";
							$all[]			 = array(
								'full'       => $value['source'],
								'caption'    => '',
								'subcaption' => '');

							if($per_page_count > $per_page) 
							{
								$per_page_count = 1;
							}
							
							$sizes       = $this->getWidthHeight($layout_name, $images_layout, $per_page_count);
							$wdt         = $sizes[0];
							$thumbHeight = $sizes[1];

							$opts        = array(
								"w"    => $wdt,
								"h"    => $thumbHeight,
								"q"    => 100, 
								"crop" => true );

							$thumb_url = get_image_thumb($imgFullUrl, $opts);
							//var_dump($imgFullUrl, $thumb_url);
							//list($wdt, $thumbHeight) = getimagesize($thumb_url);

							$isotopeItemsHTML .= '<div style="width: '.$wdt.'px; height: '.$thumbHeight.'px; margin: '.$gap01.'px;" class="isotopeItem _group'.$id_group.' itemm'.$per_page_count.'">
								<a class="fancybox-thumb" rel="fancybox-thumb" href="'.$imgFullUrl.'" title="'.$imageCaption.'" data-subtitle="'.$imageSubCaption.'">
									<img class="isotopeThumb" src="'.$thumb_url.'" width="'.$wdt.'" height="'.$thumbHeight.'" />
									<div class="caption">'.$imageCaption.'</div>
									<div class="subcaption">'.$imageSubCaption.'</div>
								</a>			
							</div>';
							$per_page_count++;
						}						
					}
										 					
					$groupImages = $groups[$i]->imageItems;									
					for ($j=0; $j < sizeof($groupImages); $j++) {
						$count_items++;												
						//image caption
						$imageCaption    = wptexturize($groupImages[$j]->caption);
						$imageSubCaption = wptexturize($groupImages[$j]->subcaption);
						$imageSubCaption = ($imageSubCaption != "undefined") ? $imageSubCaption : "";
						
						$attachementID   = $groupImages[$j]->attachementID;
						$thumbHeight     = $groupImages[$j]->imageHeight;	
						$thumbURL        = $groupImages[$j]->imageURL;	
						$wdt             = (intval($groupImages[$j]->imageWidth) > 0) ? intval($groupImages[$j]->imageWidth) : 250;	

						if($per_page_count > $per_page) 
						{
							$per_page_count = 1;
						}
						
						$sizes       = $this->getWidthHeight($layout_name, $images_layout, $per_page_count);
						$wdt         = $sizes[0];
						$thumbHeight = $sizes[1];
						
							
						//image full
						$imageFullArray               = wp_get_attachment_image_src($attachementID, 'full');
						($imageFullArray)?$imgFullUrl = $imageFullArray[0]:$imgFullUrl='http://placehold.it/1000x800';
						$all[]                        = array(
								'full'       => $imgFullUrl,
								'caption'    => $imageCaption,
								'subcaption' => $imageSubCaption);
						//thumb
						$thumb_url = 'http://placehold.it/'.$wdt.'x'.$thumbHeight;
						if($imageFullArray){
							$thumb_temp_url = rx_resize($imgFullUrl, $wdt, $thumbHeight, true);
							($thumb_temp_url)?$thumb_url = $thumb_temp_url:$thumb_url=$thumb_url;
						}

						
						if($thumbURL != '')
						{
							$isotopeItemsHTML .= '<div style="width: '.$wdt.'px; height: '.$thumbHeight.'px; margin: '.$gap01.'px;" class="isotopeItem _group'.$id_group.' itemm'.$per_page_count.'">
								<a href="'.$thumbURL.'" title="'.$imageCaption.'" data-subtitle="'.$imageSubCaption.'">
									<img class="isotopeThumb" src="'.$thumb_url.'" width="'.$wdt.'" height="'.$thumbHeight.'" />
									<div class="caption">'.$imageCaption.'</div>
									<div class="subcaption">'.$imageSubCaption.'</div>
								</a>												
							</div>';		
						}
						else
						{
							$isotopeItemsHTML .= '<div style="width: '.$wdt.'px; height: '.$thumbHeight.'px; margin: '.$gap01.'px;" class="isotopeItem _group'.$id_group.' itemm'.$per_page_count.'">
								<a class="fancybox-thumb" rel="fancybox-thumb" href="'.$imgFullUrl.'" title="'.$imageCaption.'" data-subtitle="'.$imageSubCaption.'">
									<img class="isotopeThumb" src="'.$thumb_url.'" width="'.$wdt.'" height="'.$thumbHeight.'" />
									<div class="caption">'.$imageCaption.'</div>
									<div class="subcaption">'.$imageSubCaption.'</div>
								</a>												
							</div>';			
						}
						
						$per_page_count++;			
					}									
				}
				$isotopeItemsHTML_all = "";
				$per_page_count = 1;
				foreach ($all as $key => $value) 
				{
					$thumb_url       = $value['full'];
					$imgFullUrl      = $value['full'];
					$imageCaption    = $value['caption'];
					$imageSubCaption = $value['subcaption'];

					if($per_page_count > $per_page) 
					{
						$per_page_count = 1;
					}
					
					$sizes       = $this->getWidthHeight($layout_name, $images_layout, $per_page_count);
					$wdt         = $sizes[0];
					$thumbHeight = $sizes[1];

					$opts        = array(
						"w"    => $wdt,
						"h"    => $thumbHeight,
						"q"    => 100, 
						"crop" => true );

					$thumb_url = get_image_thumb($imgFullUrl, $opts);
					

					$isotopeItemsHTML_all .= '<div style="width: '.$wdt.'px; height: '.$thumbHeight.'px; margin: '.$gap01.'px;" class="isotopeItem group_all itemm'.$per_page_count.'">
						<a class="fancybox-thumb" rel="fancybox-thumb" href="'.$imgFullUrl.'" title="'.$imageCaption.'" data-subtitle="'.$imageSubCaption.'">
							<img class="isotopeThumb" src="'.$thumb_url.'" width="'.$wdt.'" height="'.$thumbHeight.'" />
							<div class="caption">'.$imageCaption.'</div>
							<div class="subcaption">'.$imageSubCaption.'</div>
						</a>			
					</div>';
					$per_page_count++;
				}
				// foreach ($all as $key => $value) 
				// {
				// 	if($per_page_count > $per_page) 
				// 	{
				// 		$per_page_count = 1;
				// 	}
					
				// 	if(isset($images_layout[$layout_name][$per_page_count]['width']))
				// 	{
				// 		$wdt = $images_layout[$layout_name][$per_page_count]['width'];
				// 	}

				// 	if(isset($images_layout[$layout_name][$per_page_count]['height']))
				// 	{
				// 		$thumbHeight = $images_layout[$layout_name][$per_page_count]['height'];
				// 	}
					
				// 	$opts = array(
				// 		"w"    => $wdt,
				// 		"h"    => $thumbHeight,
				// 		"q"    => 100, 
				// 		"crop" => true );

				// 	$thumb_url       = get_image_thumb($value['full'], $opts);
				// 	$imageCaption    = $value['caption'];
				// 	$imageSubCaption = $value['subcaption'];

				// 	$isotopeItemsHTML_all .= '<div style="width: '.$wdt.'px; height: '.$thumbHeight.'px; margin: '.$gap01.'px;" class="isotopeItem group_all itemm'.$per_page_count.'">
				// 		<img class="isotopeThumb" src="'.$thumb_url.'" width="'.$wdt.'" height="'.$thumbHeight.'" />
				// 		<div class="isotopeItemOverlay" data-indx="'.$count_items.'" data-full_url="'.$value['full'].'" style="background-color: #'.$itemsColor.';background: rgba('.$rgba[0].', '.$rgba[1].', '.$rgba[2].', .15);">
				// 			<div class="rx_isotope_beacon"  data-lightbox="'.$lightbox.'">
				// 				<div class="caption">'.$imageCaption.'</div>
				// 				<div class="subcaption">'.$imageSubCaption.'</div>										
				// 			</div>
				// 			<p class="isotopeItemCaption">'.$imageCaption.'</p>							
				// 		</div>						
				// 	</div>';
				// 	$per_page_count++;
				// }

				$isotopeMenuHTML .= '</ul>';
				
				$return_val = '<div class="rx_isotope_ui">';
				
					$return_val .= $isotopeMenuHTML;
					$return_val .= '<div class="isotope_top_space"></div>';
					$return_val .= '<div class="isotopeContainer">';
					$return_val .= $isotopeItemsHTML_all.$isotopeItemsHTML;
					$return_val .= '</div>';
				
				$return_val .= '</div>';
				
			}
			else
			{
				$return_val = 'gallery meta data not found';
			}
		}
		else
		{
			$return_val = 'gallery not found';
		}
				
		return $return_val;		
	}

	private function getWidthHeight($layout_name, $images_layout, $per_page_count)
	{
		if($layout_name == 'amenities')
		{
			if(isset($images_layout[$layout_name][$per_page_count]['width']))
			{
				$wdt = $images_layout[$layout_name][$per_page_count]['width'];
			}

			if(isset($images_layout[$layout_name][$per_page_count]['height']))
			{
				$thumbHeight = $images_layout[$layout_name][$per_page_count]['height'];
			}	
		}
		else
		{
			$wdt         = self::G_IMG_WIDTH;
			$thumbHeight = self::G_IMG_HEIGHT;
		}
		return array($wdt, $thumbHeight);
	}
	
	//utils - convert hex to rgb	
	protected function html2rgb($color)
	{
	    if ($color[0] == '#')
	        $color = substr($color, 1);
	    if (strlen($color) == 6)
	        list($r, $g, $b) = array($color[0].$color[1],
	                                 $color[2].$color[3],
	                                 $color[4].$color[5]);
	    elseif (strlen($color) == 3)
	        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
	    else
	        return false;
	    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
	    return array($r, $g, $b);
	}					

    /**
     * Get images from Flickr photostream
     */
    public function get_flickr_photostream($instance)
    {		
		$api_key  = $instance['api_key'];
		$photoset_id  = $instance['photoset_id'];
		$flickr_per_page = $instance['flickr_per_page'];		
		$params   = array(
    		'api_key'      => $api_key,
    		'method'       => 'flickr.people.getPhotos',
    		'photoset_id'      => $photoset_id,
    		'per_page'     => $flickr_per_page,
    		'content_type' => '7',
    		'extras'	   => 'url_sq, url_o, url_t, url_s, url_m',
    		'format'       => 'php_serial'
    	);

    	$encoded_params = array();
    	foreach ($params as $k => $v)
    	{
    		$encoded_params[] = urlencode($k).'='.urlencode($v);
    	}
    	$url     = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);
    	$rsp     = file_get_contents($url);
    	$rsp_obj = unserialize($rsp);
    	// ========================================================
    	// display the photo title (or an error if it failed)
    	// ========================================================
    	return $rsp_obj["photos"]["photo"];
    }  

    /**
     * Get photos from flickr photoset
     * @param  array $instance
     * @return array
     */
    public function getFlickrPhotoSet($instance)
    {
    	$cache = $this->getCache('flickr_photos');
    	if($cache) return $cache;

		$api_key     = $instance['api_key'];
		$photoset_id = $instance['photoset_id'];
		$per_page    = intval($instance['flickr_per_page']);

    	$params = array(
			'api_key'        => $api_key,
			'method'         => 'flickr.photosets.getPhotos',
			'per_page'       => $per_page,
			'photoset_id'    => $photoset_id,
			'format'         => 'json',
			'nojsoncallback' => '1',
			'extras'         => 'url_sq, url_o, url_t, url_s, url_m');
    	$encoded_params = array();
    	foreach ($params as $k => $v)
    	{
    		$encoded_params[] = urlencode($k).'='.urlencode($v);
    	}
		$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);    
		$rsp = file_get_contents($url);
		$arr = json_decode($rsp);
		
		$this->setCache('flickr_photos', $arr->photoset->photo);

		return $arr->photoset->photo;
    }

    /**
	 * Set Cache
	 * @param string  $key    
	 * @param string  $val    
	 * @param integer $time   
	 * @param string  $prefix 
	 */
	public function setCache($key, $val, $time = 3600, $prefix = 'cheched-')
	{		
		set_transient($prefix.$key, $val, $time);
	}

	/**
	 * Get Cache
	 * @param  string $key    
	 * @param  string $prefix 
	 * @return mixed
	 */
	public function getCache($key, $prefix = 'cheched-')
	{		
		$cached   = get_transient($prefix.$key);
		if (false !== $cached) return $cached;	
		
		return false;
	}
		
}

/**
* Returns file extension or false, if it's not supported
*
* @param string url or path to image
* @return string
*/
function get_extension( $src ) {	
	$src = explode('?', $src);
	$src = $src[0];	
    $type = wp_check_filetype( $src );

    return ( isset( $type[ "ext" ] ) ) ? $type[ "ext" ] : false;
}


/**
* Returns cached, modified image.
* If image is not cached, it will create a modified image, cache it,
* then returns src to modified image.
*
*
* @param string $src Url or File path to image
* @param array $opts { {'w'=>int, 'h'=>int, 'q'=>int, 'crop'=>bool} } or "w=int&h=int&q=int&crop=bool"
* @return string
*/
function get_image_thumb( $src, $opts = null ) {
    
    //
    // Default Paramter values
    //
    $defaults = array(
        "w" => PHP_INT_MAX, // Won't resize if image width is smaller than default width
        "h" => PHP_INT_MAX, // Won't resize if image height is smaller than default height
        "q" => 95,
        "crop" => true
    );

    //
    // The default thumbnail url
    //
    $thumb_url = $src;
    
    //
    // Get the extension
    //
    $ext = get_extension( $src );

    //
    // If we can't determine the extension, don't even bother trying.
    //
    if( !$ext ) {
        return $thumb_url;
    }

    // Merge default with passed in options
    $opts = wp_parse_args( $opts, $defaults );

    // Extract paramater values
    extract( $opts, EXTR_SKIP );
    
    // Width
    $w = ( isset( $w ) && is_int( intval( $w ) ) ) ? intval( $w ) : $defaults[ "w" ];

    // Height
    $h = ( isset( $h ) && is_int( intval( $h ) ) ) ? intval( $h ) : $defaults[ "h" ];

    // Quality
    $q = ( isset( $q ) && is_int( intval( $q ) ) ) ? intval( $q ) : $defaults[ "q" ];

    // Crop
    $crop = ( isset( $crop ) && $crop ) ? $crop : $defaults[ "crop" ];
 	$crop = true;
    //$h = 9999;
    // Generate Unique Cache file
    $cache = md5( $src . "$w-$h-$q-$crop-gc" );
    
    // WordPress uploads directory (works with multi-site)
    $uploads = wp_upload_dir();

    // Cache directory path
    $cache_dir = $uploads[ "basedir" ] . "/2014/00";

    // Reset the default thumbnail url, in case it's cached.
    $thumb_url = $uploads[ "baseurl" ] . "/2014/00/$cache.$ext";
    
    // Thumbnail physical directory
    $thumb_dir = $cache_dir;

    // Thumbnail physical filename
    $thumb_file = $thumb_dir . "/$cache.$ext";


    //
    // Generate 'cache' directory if it doesn't exist yet.
    //
    if( !dir( $cache_dir ) ) {
        mkdir( $cache_dir, 0744, true );
    }

    //var_dump(is_file( $thumb_file ), $thumb_file.$src);
    //
    // Check to see if the file is cached. If not, generate the resized file.
    //
    if( !is_file( $thumb_file ) ) {

        //
        // Get the image editor object
        //
        $editor = wp_get_image_editor( $src );

        if( !is_wp_error( $editor ) ) {
                
            //
            // Resize the image
            //
           
            $editor->resize( $w, $h, $crop );

            
            //
            // Set the image quality
            //
            $editor->set_quality( $q );
            
            //
            // Save the modified file.
            //
            $editor->save( $thumb_file );

        } else {
            //
            // Something didn't go right with the editor.
            // Return original image src.
            //
            $thumb_url = $src;
        }

    }

    //
    // Return thumbnail src
    //
    return $thumb_url;

}

?>