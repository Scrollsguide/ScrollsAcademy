<?php
	class CategoryController extends BaseController {
		
		public function viewCategoryTypeAction($category){
			$cacheKey = "category_index_" . $category;
			if (($guides = $this->getCache()->get($cacheKey)) === null){
				// set up entity and repository
				$em = $this->getApp()->get("EntityManager");
				$guideRepository = $em->getRepository("Guide");
				
				// look for guides in the repo
				$guides = $guideRepository->findAllByCategory($category);
				
				$this->getCache()->set($cacheKey, $guides, 600);
			}
			
			return $this->render("guidelist.html", array(
				"guides" => $guides,
				"category" => $category,
				"title"	=> ucfirst($category),
				"excludeCategory" => $category
			));
		}
	
		public function viewGuidesByLevelAction($level){
			// temporarily use same method
			return $this->viewCategoryTypeAction($level);
		}
		
	}