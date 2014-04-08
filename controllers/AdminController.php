<?php

	class AdminController extends BaseController {

		public function __construct(App $app) {
			parent::__construct($app);
			// don't cache the admin pages
			$this->setCacheRules(array(
				"cache" => false
			));

		}

		public function loginAction() {
			if ($this->getApp()->getSession()->getUser()->isLoggedIn()) {
				// user is already logged in, redirect to homepage
				$loginRoute = $this->getApp()->getRouter()->getRoute("admin_index");

				return new RedirectResponse($loginRoute->get("path"));
			}

			return $this->render("admin/login.html");
		}

		// contains POST login information
		public function doLoginAction() {
			$r = $this->getApp()->getRequest();

			$username = $r->getParameter("username");
			$password = $r->getParameter("password");

			$bag = $this->getApp()->getSession()->getFlashBag();
			if (empty($username)) {
				$bag->add("admin_login_message", "Fill out a username.");

				return $this->toLogin();
			}
			if (empty($password)) {
				$bag->add("admin_login_message", "Fill out a password.");

				return $this->toLogin();
			}

			// set up Account Provider
			$accountProviderName = $this->getApp()->getConfig()->get("accountprovider") . "AccountProvider";
			$sgAccount = new $accountProviderName($this->getApp());

			if (!$this->getApp()->getSession()->getUser()->login($sgAccount, $username, $password)) {
				$bag->add("admin_login_message", "Wrong password or nonexistent user.");

				return $this->toLogin();
			}

			$loginRoute = $this->getApp()->getRouter()->getRoute("admin_index");

			return new RedirectResponse($loginRoute->get("path"));
		}

		public function doLogoutAction() {
			$this->getApp()->getSession()->getUser()->logout();
			$this->getApp()->getSession()->getFlashBag()->add("admin_login_message", "Bye!");

			return $this->toLogin();
		}

		public function indexAction() {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");

			// look for guides in the repo
			$guides = $guideRepository->findAll();

			return $this->render("admin/index.html", array(
				"guides" => $guides
			));
		}

		public function editGuideAction($url) {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");

			// look for guides in the repo
			if (($guide = $guideRepository->findOneBy("url", $url)) !== false) {
				return $this->render("admin/edit_guide.html", array(
					"guide" => $guide
				));
			} else { // guide not found in the repository
				$r = new HtmlResponse();
				$r->setContent("Guide not found");
			}

			return $r;
		}

		public function doSaveAction() {
			$r = $this->getApp()->getRequest();

			$guideId = $r->getParameter("id");
			$title = $r->getParameter("title");
			$summary = $r->getParameter("summary");
			$content = $r->getParameter("content");

			$newUrl = URLUtils::makeBlob($title);

			// convert markdown to html
			$this->getApp()->getClassloader()->addDirectory("libs/Markdown");

			$htmlFromMarkdown = Markdown::defaultTransform($content);

			$out = new HtmlResponse();
			$out->setContent($htmlFromMarkdown);

			return $out;
		}

		private function userPerms() {
			return $this->getApp()->getSession()->getUser()->isLoggedIn();
		}

		// redirects to admin login page
		private function toLogin() {
			$loginRoute = $this->getApp()->getRouter()->getRoute("admin_login");

			return new RedirectResponse($loginRoute->get("path"));
		}
	}