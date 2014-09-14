<?php

	class UserController extends BaseController {
	
		public function __construct(App $app) {
			parent::__construct($app);
			// don't cache the user pages
			$this->setCacheRules(array(
				"cache" => false
			));
		}

		public function loginAction() {
			return $this->render("login.html", array(
				"title" => "User login"
			));
		}
		
		// contains POST login information
		public function doLoginAction() {
			$r = $this->getApp()->getRequest();

			$username = $r->getParameter("username");
			$password = $r->getParameter("password");

			$bag = $this->getApp()->getSession()->getFlashBag();
			if (empty($username)) {
				$bag->add("login_message", "Fill out a username.");

				return $this->toLogin();
			}
			if (empty($password)) {
				$bag->add("login_message", "Fill out a password.");

				return $this->toLogin();
			}

			// set up Account Provider
			$accountProviderName = $this->getApp()->getConfig()->get("accountprovider") . "AccountProvider";
			$sgAccount = new $accountProviderName($this->getApp());

			if (!$this->getApp()->getSession()->getUser()->login($sgAccount, $username, $password)) {
				$bag->add("login_message", "Wrong password or nonexistent user.");

				return $this->toLogin();
			}

			$loginRoute = $this->getApp()->getRouter()->getRoute("index");

			return new RedirectResponse($loginRoute->get("path"));
		}

		public function doLogoutAction() {
			$this->getApp()->getSession()->getUser()->logout();
			$this->getApp()->getSession()->getFlashBag()->add("login_message", "Bye!");

			return $this->toLogin();
		}
	}