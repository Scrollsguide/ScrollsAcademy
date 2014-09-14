<?php

	class DemoAccountProvider extends AccountProvider {

		// implement your own authenticate function here
		// or extend AccountProvider class for your own provider
		public function authenticate($username, $password) {
			return $username === "admin" && $password === "admin";
		}

		public function getAccessLevel(User $u){
			return AccessLevel::user();
		}
	}