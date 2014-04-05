<?php
	class URL {
	
		private $path;
	
		public function __construct($path){
			$this->path = $path;
		}
		
		public function getPath($includeGetParams = true){
			if ($includeGetParams){
				return $this->path;
			} else {
				// parse url and return path without any parameters or hashes
				$pathInfo = parse_url($this->path);
				return $pathInfo['path'];
			}
		}
	}