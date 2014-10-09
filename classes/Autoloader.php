<?php

	class Autoloader {

		private $baseDir;

		// list of directories which can contain classes
		private $dirs = array();

		/**
		 * @param null $baseDir
		 * @return Autoloader
		 */
		public static function register($baseDir = null) {
			$loader = new self($baseDir);
			// register the autoloader
			spl_autoload_register(array($loader, 'loadClass'));

			return $loader;
		}

		public function __construct($baseDir = null) {
			if ($baseDir === null) {
				$baseDir = dirname(__FILE__) . "/..";
			}

			$this->baseDir = $baseDir;
		}

		/**
		 * Add directory for autoloading.
		 * Set recursive to true to add subdirectories as well.
		 */
		public function addDirectory($dir, $recursive = false) {
			$absPath = $this->baseDir . "/" . $dir;
			$this->dirs[] = $absPath;

			if ($recursive) {
				$children = array_filter(glob($absPath . "/*"), 'is_dir');

				foreach ($children as $c) {
					// recursively add every subdirectory
					$this->addDirectory($dir . "/" . pathinfo($c, PATHINFO_BASENAME), true);
				}
			}
		}

		public function loadClass($class) {
			// remove namespace from class
			if (($slashPos = strrpos($class, '\\')) !== false) {
				$class = substr($class, $slashPos + 1);
			}

			$found = false;
			for ($i = 0; $i < count($this->dirs) && !$found; $i++) {
				$file = sprintf('%s/%s.php', $this->dirs[$i], $class);

				$found = $this->tryRequire($file);
			}

			if (!$found) {
				// this won't find classes from the /libs dir, so don't throw an exception
				// just yet, other autoloaders might find something
				// throw new Exception(sprintf("Class '%s' could not be loaded.", $class));
			}
		}

		public function tryRequire($file) {
			if (is_file($file)) {
				require $file;

				return true;
			}

			return false;
		}
	}