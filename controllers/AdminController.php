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
				$indexRoute = $this->getApp()->getRouter()->getRoute("admin_index");

				return new RedirectResponse($indexRoute->get("path"));
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

		public function newGuideAction(){
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");

			$allCategories = $guideRepository->findAllCategories();

			return $this->render("admin/edit_guide.html", array(
				"categories" => $allCategories
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
				// map guide categories to categories
				$guideCategories = $guideRepository->findGuideCategories($guide);
				$allCategories = $guideRepository->findAllCategories();

				foreach ($allCategories as $key => $c){
					$contains = false;
					for ($i = 0; $i < count($guideCategories) && !$contains; $i++){
						$contains |= $guideCategories[$i]['name'] === $c['name'];
					}
					$allCategories[$key]['in'] = $contains;
				}

				return $this->render("admin/edit_guide.html", array(
					"guide" => $guide,
					"categories" => $allCategories
				));
			} else { // guide not found in the repository
				$r = new HtmlResponse();
				$r->setContent("Guide not found");
			}

			return $r;
		}

		public function editHomepageAction($id) {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$homepageRepository = $em->getRepository("Homepage");

			if (($homepage = $homepageRepository->findOneBy("id", $id)) !== false) {
				$blocks = $homepageRepository->findHomepageBlocks($homepage);

				foreach ($blocks as $block) {
					$homepage->addBlock($block);
				}

				return $this->render("admin/edit_homepage.html", array(
					"homepage" => $homepage
				));
			} else {
				$r = new HtmlResponse();
				$r->setContent("Homepage not found");
			}
			return $r;
		}

		public function doHomepageSaveAction() {
			$r = $this->getApp()->getRequest();

			$blocks = $r->getParameter('blocks');

			$h = new Homepage();

			if (($homepageId = $r->getParameter("homepageid", 0)) !== 0){
				// edit homepage
				$h->setId($homepageId);
			}

			foreach ($blocks as $block) {
				$h->addBlock($block);
			}

			$em = $this->getApp()->get("EntityManager");
			$homepageRepository = $em->getRepository("Homepage");

			$homepageRepository->persist($h);

			$this->getApp()->getSession()->getFlashBag()->add("homepage_message", "Homepage saved.");

			// redirect to homepage
			$indexRoute = $this->getApp()->getRouter()->getRoute("admin_index");

			return new RedirectResponse($indexRoute->get("path"));
		}

		public function doSaveAction() {
			$r = $this->getApp()->getRequest();

			$title = $r->getParameter("title");
			$content = $r->getParameter("content");

			$g = new Guide();
			if (($guideId = $r->getParameter("guideid", 0)) !== 0){
				// edit guide
				$g->setId($guideId);
			} else {
				// make new guide, so don't set id in guide
			}
			$g->setTitle($title);
			$g->setSummary($r->getParameter("summary"));
			$g->setURL(URLUtils::makeBlob($title));
			$g->setAuthor($r->getParameter("author"));
			$g->setMarkdown($content);

			// convert markdown to html
			$this->getApp()->getClassloader()->addDirectory("libs/Markdown");

			$htmlFromMarkdown = Markdown::defaultTransform($content);
			$g->setContent($htmlFromMarkdown);

			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			// load categories and check whether they're toggled or not
			$categories = $guideRepository->findAllCategories();

			foreach ($categories as $category){
				if ($r->getParameter("category_" . $category['id'], 0) === "on"){
					$g->addCategory($category['id']);
				}
			}

			$guideRepository->persist($g);

			$this->getApp()->getSession()->getFlashBag()->add("guide_message", "Guide saved.");

			// redirect to homepage
			$indexRoute = $this->getApp()->getRouter()->getRoute("admin_index");

			return new RedirectResponse($indexRoute->get("path"));
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