<?php
	class AdminController extends Controller {
		
		public function __construct(App $app){
			parent::__construct($app);
			// don't cache the admin pages
			$this->setCacheRules(array(
				"cache" => false
			));
			
		}
		
		public function loginAction(){
			return $this->render("admin/login.html");
		}
		
		// contains POST login information
		public function doLoginAction(){
		
		}
		
		public function indexAction(){
			if (!$this->userPerms()){
				$this->toLogin();
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
		
		public function editGuideAction($url){
			if (!$this->userPerms()){
				return $this->toLogin();
			}
			
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			
			// look for guides in the repo
			if (($guide = $guideRepository->findOneBy("url", $url)) !== false){ 
				return $this->render("admin/edit_guide.html", array(
					"guide" => $guide
				));
			} else { // guide not found in the repository
				$r = new HtmlResponse();
				$r->setContent("Guide not found");
			}
			
			return $r;
		}
		
		private function userPerms(){
			return $this->getApp()->getSession()->getUser()->isLoggedIn();
		}
		
		// redirects to admin login page
		private function toLogin(){
			$loginRoute = $this->getApp()->getRouter()->getRoute("admin_login");
			return new RedirectResponse($loginRoute->get("path"));
		}
	}