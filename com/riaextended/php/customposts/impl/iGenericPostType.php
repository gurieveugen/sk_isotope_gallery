<?php

interface IGGenericPostTypeRX{
	
	public function create($cptHelper, $settings);
	public function getSettings();
	public function getPostSlug();
}


?>