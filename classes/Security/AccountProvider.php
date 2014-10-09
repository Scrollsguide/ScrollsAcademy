<?php

	abstract class AccountProvider {

		private $app;

		public function __construct(App $app) {
			$this->app = $app;
		}

		protected function getApp() {
			return $this->app;
		}

		/**
		 * Extend this class and add it to config.ini for integration with
		 * your website.
		 *
		 * @param $username
		 * @param $password
		 * @return true if user exists and password is correct, false otherwise
		 */
		public abstract function authenticate($username, $password);

		public abstract function getAccessLevel(User $user);

		public abstract function callback(User $user);

	}