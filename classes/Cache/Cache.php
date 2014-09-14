<?php

	class Cache {

		private $app;

		private $basePath;

		public function __construct(App $app, $path) {
			$this->app = $app;

			// make sure cache directory isn't outside app path
			if (!$this->in_root($path)) {
				throw new Exception(sprintf("Cache path '%s' not in app root.", $path));
			}

			$this->basePath = $path;
		}

		private function in_root($path) {
			if (($realPath = realpath($path)) !== false) {
				// check whether cache base equals app root
				if (substr($realPath, 0, strlen($this->app->getBaseDir())) !== $this->app->getBaseDir()) {
					return false;
				}

				// cache is in app root, continue
				return true;
			} else {
				// realpath returns false if file does not exist
				return false;
			}
		}

		public function exists($file) {
			$absPath = $this->getPathForFile($file);

			if (!$this->in_root($absPath)) {
				return false;
			}

			return is_file($absPath);
		}

		public function isValid($key, $ttl = 300) {
			$absPath = $this->getPathForFile($key);

			$exists = $this->exists($key);
			if (!$exists) {
				return false;
			}

			// file exists, just check the time now
			return (time() - filemtime($absPath)) < $ttl;
		}

		public function save($path, $content) {
			$absPath = $this->getPathForFile($path);

			$parent = dirname($absPath);
			$this->prepareDirectory($parent);

			$fileHandle = fopen($absPath, "w");
			fwrite($fileHandle, $content);
		}

		public function load($path) {
			if (!$this->exists($path)) {
				return false;
			}

			$absPath = $this->getPathForFile($path);

			return file_get_contents($absPath);
		}

		public function prepareDirectory($path) {
			if (!is_dir($path)) {
				// recursive mkdir
				mkdir($path, 0777, true);
			}
		}

		public function getPathForFile($file) {
			$fullPath = $this->basePath . "/" . $file;

			return $fullPath;
		}

		public function remove($file) {
			if ($this->exists($file)) {
				$absPath = $this->getPathForFile($file);

				// delete file
				unlink($absPath);
			}
		}

		public function removeDir($file) {
			$absPath = $this->getPathForFile($file);

			if (!$this->in_root($absPath)) {
				return;
			}

			// make sure rightmost char is dir separator
			if (substr($absPath, -1) !== DIRECTORY_SEPARATOR) {
				$absPath .= DIRECTORY_SEPARATOR;
			}

			if (is_dir($absPath)) {
				$this->remove_recursive($absPath);
			}
		}

		private function remove_recursive($target) {
			if (is_dir($target)) {
				$files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

				foreach ($files as $file) {
					$this->remove_recursive($file);
				}

				rmdir($target);
			} else if (is_file($target)) {
				unlink($target);
			}
		}

	}