<?php

	class DemoAccountProvider extends AccountProvider {

		// implement your own authenticate function here
		// or extend AccountProvider class for your own provider
		public function authenticate($username, $password) {
			return ($username === "admin" && $password === "admin") ||
					($username === "user" && $password === "user");
		}

		public function getAccessLevel(User $u){
			if ($u->getUsername() === "admin"){
				return AccessLevel::admin();
			} else {
				return AccessLevel::user();
			}
		}
	}