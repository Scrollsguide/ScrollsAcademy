<?php

	class AdminController extends BaseController {

		const MAXUPLOADSIZE = 1024000; //5 megs
		const USERIMAGEDIRECTORY = '/public_html/assets/images/user-imgs/';

		public function indexAction() {
			return $this->viewByReviewStatus(1);
		}

		public function viewReviewsAction() {
			return $this->viewByReviewStatus(0);
		}

		public function viewByReviewStatus($reviewStatus) {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");

			// look for guides in the repo
			$guides = $guideRepository->findAllBy("reviewed", $reviewStatus);

			return $this->render("admin/index.html", array(
				"title"  => "Academy admin",
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
				"title"      => "New guide",
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

			// look for guide in the repo
			if (($guide = $guideRepository->findOneBy("url", $url)) !== null) {
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
					"title"      => "Edit guide",
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

			if (($homepage = $homepageRepository->findOneBy("id", $id)) !== null) {
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
					"title"    => "Admin index",
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

			// clear rendered index html page from cache so it's refreshed immediately
			$route = $this->getApp()->getRouter()->getRoute("index");
			$cacheKey = RouteHelper::getCacheKey($route);
			$this->getApp()->getCache()->remove("Pages/" . $cacheKey);

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
			$guideId = $r->getParameter("guideid", 0);

			$g = $this->saveAction($guideId);

			// clear rendered guide html page from cache so it's refreshed immediately
			// TODO: clear cache for this guide in any series
			$route = $this->getApp()->getRouter()->getRoute("view_guide");
			$route->set("urlMatch", array($g->getUrl()));

			$cacheKey = RouteHelper::getCacheKey($route);
			$this->getApp()->getCache()->remove("Pages/" . $cacheKey);

			// set save message
			$this->getApp()->getSession()->getFlashBag()->add("guide_message", "Guide saved. --
			Some pages are cached, it can take some time before changes are reflected on the frontend --");

			// return to edit guide page
			$guideRoute = $this->getApp()->getRouter()->generateUrl("admin_edit_guide", array("title" => $g->getUrl()));

			return new RedirectResponse($guideRoute);
		}

		/**
		 * @param $guideId
		 * @param bool $review Is guide submitted for review?
		 * @return Guide
		 */
		public function saveAction($guideId, $review = false) {
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			$r = $this->getApp()->getRequest();

			$title = $r->getParameter("title");
			$content = $r->getParameter("content");

			$g = new Guide();
			if ($guideId !== 0) {
				$g->setId($guideId);
			} else {
				// new guide, don't set id
			}

			// load image and banner
			$images = $r->getParameter("images");

			$g->setTitle($title);
			$g->setSummary($r->getParameter("summary"));

			$url = GuideHelper::makeURL($g, $guideRepository);

			$g->setUrl($url);
			$g->setMarkdown($content);

			if ($review) {
				$g->setAuthor($this->getApp()->getSession()->getUser()->getUsername());
				$g->setStatus(GuideStatus::VISIBLE_WITH_URL);
				$g->setSynopsis("");
				$g->setVideo("");
				$g->setDiscussion("");
				$g->setReviewed(0);

				// clear images, shouldn't be in there anyway
				$images = array("", "");
			} else {
				$g->setAuthor($r->getParameter("author"));
				$g->setStatus($r->getParameter("status"));
				$g->setSynopsis($r->getParameter("synopsis"));
				$g->setVideo($r->getParameter("video"));
				$g->setDiscussion($r->getParameter("discussion"));
				$g->setReviewed(1);
			}
			$g->setImage($images[0]);
			$g->setBanner($images[1]);

			// convert markdown to html
			// don't just require the markdown class as it needs more than one
			// file to run properly, so add entire directory
			$this->getApp()->getClassloader()->addDirectory("libs/Markdown");

			$htmlFromMarkdown = MarkdownExtra::defaultTransform($content);
			$g->setContent($htmlFromMarkdown);

			// load categories and check whether they're toggled or not
			$categories = $guideRepository->findAllCategories();

			foreach ($categories as $category) {
				if ($r->getParameter("category_" . $category['id'], 0) === "on") {
					$g->addCategory($category['id']);
				}
			}

			$guideRepository->persist($g);

			return $g;
		}

		public function precompileGuideAction() {
			$g = new Guide();

			$guideMarkdown = $this->getApp()->getRequest()->getParameter("guide");

			$this->getApp()->getClassloader()->addDirectory("libs/Markdown");

			$htmlFromMarkdown = MarkdownExtra::defaultTransform($guideMarkdown);

			$g->setContent($htmlFromMarkdown);

			return $this->render("guide.html", array(
				"guide"   => $g,
				"title"   => 'Guide Preview',
				"preview" => true
			));
		}

		public function seriesAction() {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$seriesRepository = $em->getRepository("Series");

			// look for series in the repo
			$series = $seriesRepository->findAll();

			return $this->render("admin/series_index.html", array(
				"title"  => "Series",
				"series" => $series
			));
		}

		public function newSeriesAction() {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			$allGuides = $guideRepository->findAll();

			return $this->render("admin/edit_series.html", array(
				"title"     => "New series",
				"allGuides" => $allGuides
			));
		}

		public function editSeriesAction($url) {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$seriesRepository = $em->getRepository("Series");

			// look for series in the repo
			if (($series = $seriesRepository->findOneBy("url", $url)) !== null) {
				// load guides for series
				$guideRepository = $em->getRepository("Guide");
				$guides = $guideRepository->findAllBySeries($series);
				$allGuides = $guideRepository->findAll();

				return $this->render("admin/edit_series.html", array(
					"title"     => "Edit series",
					"series"    => $series,
					"guides"    => $guides,
					"allGuides" => $allGuides
				));
			} else { // series not found in the repository
				$r = new HtmlResponse();
				$r->setContent("Series not found");
			}

			return $r;
		}

		public function doSaveSeriesAction() {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			$r = $this->getApp()->getRequest();

			$title = $r->getParameter("title");

			$s = new Series();
			if (($seriesId = $r->getParameter("seriesid", 0)) !== 0) {
				// edit series
				$s->setId($seriesId);
			} else {
				// make new series, so don't set id in series
			}

			// load image and banner
			$images = $r->getParameter("images");

			$s->setTitle($title);
			$s->setSummary($r->getParameter("summary"));
			$s->setURL(URLUtils::makeBlob($title));
			$s->setImage($images[0]);
			$s->setBanner($images[1]);

			// add guides
			foreach ($r->getParameter("guides") as $guide) {
				if ($guide != 0) {
					$s->addGuide($guide);
				}
			}

			$em = $this->getApp()->get("EntityManager");
			$seriesRepository = $em->getRepository("Series");

			$seriesRepository->persist($s);

			// clear rendered series html page from cache so it's refreshed immediately
			$route = $this->getApp()->getRouter()->getRoute("view_series");
			$route->set("urlMatch", array($s->getUrl()));

			$cacheKey = RouteHelper::getCacheKey($route);
			$this->getApp()->getCache()->remove("Pages/" . $cacheKey);

			// set save message
			$this->getApp()->getSession()->getFlashBag()->add("series_message", "Series saved. --
			Some pages are cached, it can take some time before changes are reflected on the frontend --");

			// return to edit series page
			$seriesRoute = $this->getApp()->getRouter()->generateUrl("admin_edit_series", array("title" => $s->getUrl()));

			return new RedirectResponse($seriesRoute);
		}

		public function uploadImageAction() {
			if (!$this->getApp()->getSession()->getUser()->checkAccessLevel(AccessLevel::USER)) {
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

		public function settingsAction() {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			return $this->render("admin/settings.html", array(
				"title" => "Admin settings"
			));
		}

		public function clearCacheAction() {
			if (!$this->userPerms()) {
				return $this->toLogin();
			}

			$cacheType = $this->getApp()->getRequest()->getParameter("cache", "");

			if ($cacheType === "twig") {
				$this->getApp()->getCache()->removeDir("TwigViews");
			} else if ($cacheType === "routing") {
				$this->getApp()->getCache()->removeDir("Routing");
			} else if ($cacheType === "html") {
				$this->getApp()->getCache()->removeDir("Pages");
			} else { // asset cache
				// set up cache for resources directory
				$cache = new Cache($this->getApp(), $this->getApp()->getBaseDir() . ResourceController::ASSET_CACHE);

				if ($cacheType === "css") {
					$cache->removeDir("css");
				} else if ($cacheType === "js") {
					$cache->removeDir("js");
				}
			}

			$this->getApp()->getSession()->getFlashBag()->add("settings_message", "Cache cleared.");

			$settingsRoute = $this->getApp()->getRouter()->getRoute("admin_settings");

			return new RedirectResponse($settingsRoute->get("path"));
		}

		private function userPerms() {
			$u = $this->getApp()->getSession()->getUser();

			return $u->checkAccessLevel(AccessLevel::ADMIN);
		}
	}