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
	
	class URLUtils {
	
		public static function makeBlob($str, $separator = "-"){
			$str = str_replace("'", "", strtolower($str)); // don't -> dont instead of don-t
			$str = preg_replace("#[^a-z0-9" . $separator . "]#", $separator, $str);
			
			return preg_replace("#" . $separator . "+#", $separator, trim($str, $separator));
		}
		
	}