<?php

	abstract class CacheDriver {

		private $options = array();

		public function __construct($options = array()) {
			$this->options = $options;
		}

		public function getOption($key) {
			if (!isset($this->options[$key])) {
				throw new Exception(sprintf("Option '%s' not set for cache driver.", $key));
			}

			return $this->options[$key];
		}

		abstract public function get($key);

		abstract public function set($key, $value, $ttl = 300);

		abstract public function delete($key);

		abstract public function clean();

	}