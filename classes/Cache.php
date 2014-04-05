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
			if (!is_dir($parent)){
				mkdir($parent, 0777, true);
			}
			
			$fileHandle = fopen($absPath, "w");
			fwrite($fileHandle, $content);
		}
		
		public function getPathForFile($file){
			return $this->basePath . "/" . $file;
		}
		
	}