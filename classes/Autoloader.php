<?php
	class Autoloader {
		
		private $baseDir;
		
		// list of directories which can contain classes
		private $dirs = array();
		
		public static function register($baseDir = null){
			$loader = new self($baseDir);
			// register the autoloader
			spl_autoload_register(array($loader, 'loadClass'));

			return $loader;
		}
		
		public function __construct($baseDir = null){
			if ($baseDir === null){
				$baseDir = dirname(__FILE__) . "/..";
			}
			
			$this->baseDir = $baseDir;
		}
		
		public function addDirectory($dir){
			$this->dirs[] = $this->baseDir . "/" . $dir;
		}
		
		public function loadClass($class){
			$found = false;
			for ($i = 0; $i < count($this->dirs) && !$found; $i++){		
				$file = sprintf('%s/%s.php', $this->dirs[$i], $class);
				
				$found = $this->tryRequire($file);
			}
			
			if (!$found){
				// this won't find classes from the /libs dir, so don't throw an exception
				// just yet, other autoloaders might find something
				// throw new Exception(sprintf("Class '%s' could not be loaded.", $class));
			}
		}
		
		public function tryRequire($file){
			if (is_file($file)){
				require $file;
				return true;
			}
			return false;
		}
	}