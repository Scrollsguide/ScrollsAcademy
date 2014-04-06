<?php
	class Cache {
		
		private $basePath;
		
		public function __construct($path){
			$this->basePath = $path;
		}
		
		public function exists($file){
			return is_file($this->getPathForFile($file));
		}
		
		public function isValid($key, $ttl = 300){
			$absPath = $this->getPathForFile($key);
			
			$exists = $this->exists($key);
			if (!$exists){
				return false;
			}
			
			// file exists, just check the time now
			return (time() - filemtime($absPath)) < $ttl;
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