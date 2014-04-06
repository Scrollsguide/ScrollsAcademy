<?php
	class CategoryController extends Controller {
		
		public function viewCategoryTypeAction($category){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");
			
			// look for guides in the repo
			$guides = $guideRepository->findAllByCategory($category);
			
			return $this->render("guidelist.html", array(
				"guides" => $guides
			));
		}
	
		public function viewGuidesByLevelAction($level){
			// temporarily use same method
			return $this->viewCategoryTypeAction($level);
		}
		
	}