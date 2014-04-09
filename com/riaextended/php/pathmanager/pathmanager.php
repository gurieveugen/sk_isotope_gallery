<?php

class SKPathManager{
	
	private $pathsData;
	function __construct($plugin_name=''){
		$this->pathsData = array();		
	}
	
	public function addPath($name, $path){
		array_push($this->pathsData, array('name'=>$name, 'path'=>$path));
	}
	
	public function getPath($name){
		$path = array('name'=>'NULL', 'path'=>'NULL');
		for ($i=0; $i < sizeof($this->pathsData); $i++) {			
			if($this->pathsData[$i]['name']==$name){
				$path = $this->pathsData[$i];
				break;
			}
		}
		return $path;
	}
	
	public function removePath($name, $id){
		//TBD
	}
	
	private static $instance;
	public static function getInstance(){
		if(!isset(self::$instance)){
			self::$instance = new SKPathManager();
		}
		return self::$instance;
	}
}

?>