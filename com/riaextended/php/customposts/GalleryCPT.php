<?php
require_once(IISOTOPE_CLASS_PATH.'/com/riaextended/php/customposts/GenericPostType.php');
/**
 * Youtube CPT
 */
class isotopeGalleryCPT extends RXGenericPostType {
	
	/* VIDEO CONTAINER
	================================================== */
	public function groups_metabox(){
		global $post;
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $post_id;
			
			
			$custom = get_post_meta($post->ID, $this->getPostSlug().'-data', false);			
			//main json data
			$mainJSONData = "";
			
			if(isset($custom[0])){
				$mainJSONData = (isset($custom[0]['mainJSONData']))?$mainJSONData = $custom[0]['mainJSONData']:"";
			}				
		?>
		
	<!--boxes wrapper-->
	<div class="metabox_wrapper">
									
		<!--add group-->
		<div class="contentBox">			
			<a id="addGroupBTN" class="button-primary alignright"><?php _e('Add group', IISOTOPE_TEXTDOMAIN);?></a>
			<div class="vspace1"></div>
		</div>
		<!--add group-->	
		
		<!--groups-->
		<div id="accordion" class="contentBox">			
			 			 						
		</div>
		<!--groups-->		
		
		
		<!--json data-->
		<textarea id="mainJSONData" class="boxContentTextarea" name="<?php echo $this->getPostSlug().'-data'?>[mainJSONData]" rows="4"><?php echo $mainJSONData; ?></textarea>
		<!--/json data-->
		
	</div>
	<!--/boxes wrapper-->
		<?php		
	}
	
	/* PROPERTIES CONTAINER
	================================================== */
	public function properties_metabox(){
		global $post;
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $post_id;
		
			$custom_size = get_post_meta($post->ID, $this->getPostSlug().'-thumbs_size', false);
			
			$wdt = 250;
			if(isset($custom_size[0])){
				$wdt = (float)$custom_size[0]['width'];										
			}
			
			$gap01 = 5;			
			$lightbox_colors = "6abde9";
			$menu_text_color = "484848";
			$menu_back_color = "6abde9";
			$label_all = "All";

			$custom_data = get_post_meta($post->ID, $this->getPostSlug().'-extra_data', false);
			$custom      = get_post_meta($post->ID, $this->getPostSlug().'-data', false);
			if(isset($custom_data[0])){				
				$gap01 = (float)$custom_data[0]['gap01'];				
				($gap01<0)?$gap01=0:$gap01=$gap01;
				
				
				$lightbox_colors     = $custom_data[0]['lightbox_colors'];		
				$menu_text_color     = $custom_data[0]['menu_text_color'];	
				$menu_back_color     = $custom_data[0]['menu_back_color'];
				$label_all           = $custom_data[0]['label_all'];		
				$lightbox            = $custom_data[0]['lightbox'];												
				
				$api_key             = $custom_data[0]['api_key'];	
				$photoset_id         = $custom_data[0]['photoset_id'];	
				$flickr_per_page     = $custom_data[0]['flickr_per_page'];	
				$facebook_user       = $custom_data[0]['facebook_user'];	
				$facebook_group_name = $custom_data[0]['facebook_group_name'];
				$flickr_group_name   = $custom_data[0]['flickr_group_name'];
				$layout_name         = $custom_data[0]['layout_name'];
				$per_page            = $custom_data[0]['per_page'];
				$mainJSONData        = (isset($custom[0]['mainJSONData'])) ? $mainJSONData = $custom[0]['mainJSONData'] : $mainJSONData = '';
				$temp_data           = json_decode($mainJSONData);
				$groups              = $temp_data->groups;
			}
			
		?>
		
		<!--properties container-->
		<div id="propertiesContainer">
			
			<!--shortcode-->
			<div class="contentBox">
				<label class="customLabel"><?php _e('Shortcode:  ', IISOTOPE_TEXTDOMAIN);?>  <span id="shortcode" data-postid="<?php echo $post->ID;?>" class="sk_defaultText"><span><?php echo '[isotope_gallery id="'.$post->ID.'"]';?></span></span></label>
			</div>
			<!--/shortcode-->
			
			<div class="contentBoxProperties contentBoxBackground">
				<p class="contentBoxSubtitle">Thumbnails width</p>
				<div class="vspace2"></div>
                <!--thumbnail width and height-->
                <input id="spinnerThumbW" class="spinnerBox" name="<?php echo $this->getPostSlug().'-thumbs_size';?>[width]" value="<?php echo $wdt;?>" /><label class="customLabel">Width</label>                
                <!--/endthumbnail width and height--> 				
			</div>
			
			<div class="contentBoxProperties contentBoxBackground">
				<p class="contentBoxSubtitle">Thumbnails gap</p>
				<div class="vspace2"></div>
                <input id="spinnerGap01" class="spinnerBox" name="<?php echo $this->getPostSlug().'-extra_data';?>[gap01]" value="<?php echo $gap01;?>" /><label class="customLabel">Gallery gap</label>	
			</div>			
			
			<div class="contentBoxProperties contentBoxBackground">
				<p class="contentBoxSubtitle">Colors</p>
				<div class="vspace2"></div>                
                <input id="lightbox_colors" class="spinnerBox" name="<?php echo $this->getPostSlug().'-extra_data';?>[lightbox_colors]" value="<?php echo $lightbox_colors;?>" /><label class="customLabel">Lightbox buttons color</label>                
                <div class="hLine"></div>
                <input id="menu_text_color" class="spinnerBox" name="<?php echo $this->getPostSlug().'-extra_data';?>[menu_text_color]" value="<?php echo $menu_text_color;?>" /><label class="customLabel">Menu's text color</label>
                <div class="hLine"></div>
                <input id="menu_back_color" class="spinnerBox" name="<?php echo $this->getPostSlug().'-extra_data';?>[menu_back_color]" value="<?php echo $menu_back_color;?>" /><label class="customLabel">Menu's background color</label>                                 			
			</div>

			<div class="contentBoxProperties contentBoxBackground">
				<p class="contentBoxSubtitle">Flickr options</p>
				<div class="vspace2"></div>                
                <input id="api_key" name="<?php echo $this->getPostSlug().'-extra_data';?>[api_key]" value="<?php echo $api_key;?>" /><label style="display:block" class="customLabel">Api key</label>                
                <div class="hLine"></div>
                <input id="photoset_id" name="<?php echo $this->getPostSlug().'-extra_data';?>[photoset_id]" value="<?php echo $photoset_id;?>" /><label style="display:block" class="customLabel">Photoset ID</label>
                <div class="hLine"></div>
                <input id="flickr_per_page" name="<?php echo $this->getPostSlug().'-extra_data';?>[flickr_per_page]" value="<?php echo $flickr_per_page;?>" /><label style="display:block" class="customLabel">Count images</label>                                 			
                <div class="hLine"></div>
                <select name="<?php echo $this->getPostSlug().'-extra_data';?>[flickr_group_name]" id="flickr_group_name">           		
                <option value="none">none</option>
            	<?php
            		for ($i=0; $i < sizeof($groups); $i++) 
            		{
            			if($groups[$i]->name == $flickr_group_name)
            			{
            				$flickr_selected = 'selected = "selected"';
            			}
            			else
            			{
            				$flickr_selected = '';
            			}
            		?>
            			<option value="<?php echo $groups[$i]->name; ?>" <?php echo $flickr_selected; ?>><?php echo $groups[$i]->name; ?></option>
            		<?php
            		}
            	?>
                </select>
                <label style="display:block" class="customLabel">Flickr page</label>
			</div>

			<div class="contentBoxProperties contentBoxBackground">
				<p class="contentBoxSubtitle">Facebook options</p>
				<div class="vspace2"></div>                
                <input id="facebook_user" name="<?php echo $this->getPostSlug().'-extra_data';?>[facebook_user]" value="<?php echo $facebook_user;?>" /><label style="display:block" class="customLabel">Facebook user</label>                                
                <select name="<?php echo $this->getPostSlug().'-extra_data';?>[facebook_group_name]" id="facebook_group_name">                
                <option value="none">none</option>
            	<?php
            		for ($i=0; $i < sizeof($groups); $i++) 
            		{
            			if($groups[$i]->name == $facebook_group_name)
            			{
            				$facebook_selected = 'selected = "selected"';
            			}
            			else
            			{
            				$facebook_selected = '';
            			}
            		?>
            			<option value="<?php echo $groups[$i]->name; ?>" <?php echo $facebook_selected; ?>><?php echo $groups[$i]->name; ?></option>
            		<?php
            		}
            	?>
                </select>
                <label style="display:block" class="customLabel">Facebook page</label>
			</div>
			
			<div class="contentBoxProperties contentBoxBackground">
				<p class="contentBoxSubtitle">Labels</p>
				<div class="vspace2"></div>                
                <input class="spinnerBox" name="<?php echo $this->getPostSlug().'-extra_data';?>[label_all]" value="<?php echo $label_all;?>" /><label class="customLabel">Label (all)</label>                                            			
			</div>		

			<div class="contentBoxProperties contentBoxBackground">
				<p class="contentBoxSubtitle">Lyaout name</p>
				<div class="vspace2"></div>                
                <input class="spinnerBox" name="<?php echo $this->getPostSlug().'-extra_data';?>[layout_name]" value="<?php echo $layout_name;?>" /><label class="customLabel">Layout name</label>
                <div class="vspace2"></div> 
                <input class="spinnerBox" name="<?php echo $this->getPostSlug().'-extra_data';?>[per_page]" value="<?php echo $per_page;?>" /><label class="customLabel">Images per page</label>
			</div>			

			<div class="contentBoxProperties contentBoxBackground">
				<p class="contentBoxSubtitle">Lightbox</p>
				<div class="vspace2"></div>   				
				<?php
					$options_checked['yes']     = "";
					$options_checked['no']      = "";
					$options_checked[$lightbox] = 'selected';
				?>   
				<select name="<?php echo $this->getPostSlug().'-extra_data';?>[lightbox]" id="<?php echo $this->getPostSlug().'-extra_data';?>[lightbox]">
					<option value="yes" <?php echo $options_checked['yes']; ?>><?php _e('Yes'); ?></option>
					<option value="no" <?php echo $options_checked['no']; ?>><?php _e('No'); ?></option>
				</select>       
                <label class="customLabel">Enable lightbox</label>                                            			
			</div>			
											
				
		</div>
		<!--/properties container-->		
		
		
		<?php		
	}
		
}


?>