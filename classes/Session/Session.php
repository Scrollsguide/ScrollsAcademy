<?php

	class Session {

		private $user;

		private $flashbag;

		private $variables = array();

		private static $instance; // singleton

		private function __construct() {
			session_start();

			$this->user = User::createFromSession($_SESSION);
			$this->flashbag = FlashBag::createFromSession($_SESSION);
		}

		/**
		 * @return User
		 */
		public function getUser() {
			return $this->user;
		}

		/**
		 * @return FlashBag
		 */
		public function getFlashBag() {
			return $this->flashbag;
		}

		// store item in session
		public function set($key, $value) {
			$this->variables[$key] = $value;
		}

		// retrieve item stored in session
		public function get($key) {
			return isset($this->variables[$key]) ? $this->variables[$key] : null;
		}

		public function close() {
			// save state
			$_SESSION['user'] = serialize($this->user);
			$_SESSION['flashbag'] = serialize($this->flashbag);
			$_SESSION['variables'] = $this->variables;
		}

		public static function getInstance() {
			if (!isset(static::$instance)) {
				static::$instance = new static();
			}

			return static::$instance;
		}

	}