<?php

	class URL {

		private $path;

		private $https = false;

		private $host;

		public function __construct() {

		}

		public function getBaseURL() {
			return sprintf("%s://%s", $this->https ? "https" : "http", $this->host);
		}

		public function getHost() {
			return $this->host;
		}

		public function setHost($host) {
			$this->host = $host;
		}

		public function isHTTPS() {
			return $this->https;
		}

		public function setHTTPS($https) {
			$this->https = $https;
		}

		public function getPath($includeGetParams = true) {
			if ($includeGetParams) {
				return $this->path;
			} else {
				// parse url and return path without any parameters or hashes
				$pathInfo = parse_url($this->path);

				return $pathInfo['path'];
			}
		}

		public function setPath($path) {
			$this->path = $path;
		}

	}

	class URLUtils {

		public static function makeBlob($str, $separator = "-") {
			$str = str_replace("'", "", strtolower($str)); // don't -> dont instead of don-t
			$str = preg_replace("#[^a-z0-9" . $separator . "]#", $separator, $str);

			return preg_replace("#" . $separator . "+#", $separator, trim($str, $separator));
		}

	}