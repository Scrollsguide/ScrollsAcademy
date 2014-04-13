<?php

	class AdminController extends BaseController {

		const MAXUPLOADSIZE = 1024000; //5 megs
		const USERIMAGEDIRECTORY = '/public_html/assets/images/user-imgs/';

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

		public function newGuideAction() {
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

				foreach ($allCategories as $key => $c) {
					$contains = false;
					for ($i = 0; $i < count($guideCategories) && !$contains; $i++) {
						$contains |= $guideCategories[$i]['name'] === $c['name'];
					}
					$allCategories[$key]['in'] = $contains;
				}

				return $this->render("admin/edit_guide.html", array(
					"guide"      => $guide,
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

				// load guides
				$guideRepository = $em->getRepository("Guide");
				$guides = $guideRepository->findAll();

				// map guides to array with id as index
				$tplGuides = array();
				foreach ($guides as $guide) {
					$tplGuides[$guide->getId()] = $guide;
				}

				return $this->render("admin/edit_homepage.html", array(
					"homepage" => $homepage,
					"guides"   => $tplGuides
				));
			} else {
				$r = new HtmlResponse();
				$r->setContent("Homepage not found");
			}

			return $r;
		}

		public function doHomepageSaveAction() {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			$r = $this->getApp()->getRequest();

			$blocks = $r->getParameter('blocks');

			$h = new Homepage();

			if (($homepageId = $r->getParameter("homepageid", 0)) !== 0) {
				// edit homepage
				$h->setId($homepageId);
			}

			foreach ($blocks as $block) {
				// concat guides into a string
				$block['guideids'] = implode(",", $block['guides']);

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
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			$r = $this->getApp()->getRequest();

			$title = $r->getParameter("title");
			$content = $r->getParameter("content");

			$g = new Guide();
			if (($guideId = $r->getParameter("guideid", 0)) !== 0) {
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
			$g->setStatus($r->getParameter("status"));
			$g->setImage($r->getParameter("image"));

			// convert markdown to html
			// don't just require the markdown class as it needs more than one
			// file to run properly, so add entire directory
			$this->getApp()->getClassloader()->addDirectory("libs/Markdown");

			$htmlFromMarkdown = MarkdownExtra::defaultTransform($content);
			$g->setContent($htmlFromMarkdown);

			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			// load categories and check whether they're toggled or not
			$categories = $guideRepository->findAllCategories();

			foreach ($categories as $category) {
				if ($r->getParameter("category_" . $category['id'], 0) === "on") {
					$g->addCategory($category['id']);
				}
			}

			$guideRepository->persist($g);

			// clear rendered guide html page from cache so it's refreshed immediately
			$route = $this->getApp()->getRouter()->getRoute("view_guide");
			$route->set("urlMatch", array($g->getUrl()));

			$cacheKey = RouteHelper::getCacheKey($route);
			$this->getApp()->getCache()->remove("Pages/" . $cacheKey);

			// set save message
			$this->getApp()->getSession()->getFlashBag()->add("guide_message", "Guide saved.");

			// redirect to homepage
			$indexRoute = $this->getApp()->getRouter()->getRoute("admin_index");

			return new RedirectResponse($indexRoute->get("path"));
		}

		public function precompileGuideAction() {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}
			$g = new Guide();

			$guideMarkdown = $r = $this->getApp()->getRequest()->getParameter("guide");

			$this->getApp()->getClassloader()->addDirectory("libs/Markdown");

			$htmlFromMarkdown = MarkdownExtra::defaultTransform($guideMarkdown);

			$g->setContent($htmlFromMarkdown);

			return $this->render("guide.html", array(
				"guide" => $g,
				"title" => 'Guide Preview',
				"preview" => true
			));
		}

		public function uploadImageAction() {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			$request = $this->getApp()->getRequest();
			$files = $request->getFiles();
			$image = $files[0];

			$error = false;
			$r = new JsonResponse();
			if (!$image) {
				$r->setContent(array('error' => 'No file'));
				$error = true;
			}

			if (!$image['name'] || !$image['tmp_name']) {
				$r->setContent(array('error' => 'No file name'));
				$error = true;
			}

			if ($image['error']) {
				$r->setContent(array('error' => $image['error']));
				$error = true;
			}
			if ($image['size'] > (self::MAXUPLOADSIZE)) {
				$r->setContent(array('error' => 'File size too large'));
				$error = true;
			}

			$tmpPath = $image['tmp_name'];

			$isImage = @getimagesize($tmpPath) ? true : false; //ensure the file was actually an image
			if (!$isImage) {
				$r->setContent(array('error' => 'File is not a valid image'));
				$error = true;
			}

			if (!$error) {
				// all good, move it
				// avoid duplicates by just using the hash of the file as filename
				$ext = pathinfo($image['name'], PATHINFO_EXTENSION);
				$filename = md5(file_get_contents($image['tmp_name'])) . "." . $ext;

				$filePath = self::USERIMAGEDIRECTORY . $filename;
				$newLocation = $this->getApp()->getBaseDir() . $filePath;

				if (!file_exists($newLocation)) {
					move_uploaded_file($tmpPath, $newLocation);
				}
				$r->setContent(array('success' => true, 'filename' => $filename));
			}

			return $r;
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