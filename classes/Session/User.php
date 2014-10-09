<?php

	class User {

		private $accessLevel;

		private $userData;

		private $username;

		private $isLoggedIn = false;

		public function __construct() {
			$this->setAccessLevel(AccessLevel::guest());
		}

		public function login(AccountProvider $ap, $username, $password) {
			if ($ap->authenticate($username, $password)) {
				$this->username = $username;
				$this->isLoggedIn = true;

				$this->setAccessLevel($ap->getAccessLevel($this));

				$ap->callback($this);

				return true;
			} else {
				return false;
			}
		}

		public function logout() {
			$this->username = "";
			$this->isLoggedIn = false;
			$this->setAccessLevel(AccessLevel::guest());
		}

		public function isLoggedIn() {
			return $this->isLoggedIn;
		}

		public function getUsername() {
			return $this->username;
		}

		public function setAccessLevel($level) {
			$this->accessLevel = $level;
		}

		public function checkAccessLevel($level) {
			return ($this->accessLevel & $level) === $level;
		}

		public function setUserData($data) {
			$this->userData = $data;
		}

		public function getUserData($which = null) {
			if ($which !== null) {
				if (isset($this->userData[$which])) {
					return $this->userData[$which];
				} else {
					return null;
				}
			}

			return $this->userData;
		}

		// saves all user data in session
		public function save() {
			$_SESSION['user'] = serialize($this);
		}

		/**
		 * @param $sessionVars
		 * @return User
		 */
		public static function createFromSession($sessionVars) {
			if (!isset($_SESSION['user'])) {
				// create new user
				$u = new User();

				return $u;
			} else {
				return unserialize($_SESSION['user']);
			}
		}

		public function __sleep() {
			return array(
				'isLoggedIn',
				'accessLevel',
				'username',
				'userData'
			);
		}

		public function __wakeup() {

		}
	}

	class AccessLevel {

		const NONE = 1;
		const USER = 2;
		const ADMIN = 4;

		public static function guest() {
			return AccessLevel::NONE;
		}

		public static function user() {
			return AccessLevel::guest() | AccessLevel::USER;
		}

		public static function admin() {
			return AccessLevel::user() | AccessLevel::ADMIN;
		}

	}