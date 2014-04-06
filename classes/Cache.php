<?php
	class Cache {
		
		private $basePath;
		
		public function __construct($path){
			$this->basePath = $path;
		}
		
		public function exists($file){
			return is_file($this->getPathForFile($file));
		}
		
		public function save($path, $content){
			$absPath = $this->getPathForFile($path);
			
			$parent = dirname($absPath);
			$this->prepareDirectory($parent);
			
			$fileHandle = fopen($absPath, "w");
			fwrite($fileHandle, $content);
		}
		
		public function load($path){
			if (!$this->exists($path)){
				return false;
			}
			
			$absPath = $this->getPathForFile($path);
			
			return file_get_contents($absPath);
		}
		
		public function prepareDirectory($path){
			if (!is_dir($path)){
				// recursive mkdir
				mkdir($path, 0777, true);
			}
		}
		
		public function getPathForFile($file){
			return $this->basePath . "/" . $file;
		}
		
	}