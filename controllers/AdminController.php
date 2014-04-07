<?php
	class AdminController extends Controller {
		
		public function __construct(App $app){
			parent::__construct($app);
			// don't cache the admin pages
			$this->setCacheRules(array(
				"cache" => false
			));
		}
		
		public function indexAction(){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			
			// look for guides in the repo
			$guides = $guideRepository->findAll();
			
			return $this->render("admin/index.html", array(
				"guides" => $guides
			));
		}
	}