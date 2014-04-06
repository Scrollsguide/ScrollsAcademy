<?php
	class CategoryController extends Controller {
		
		public function viewCategoryTypeAction($category){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$articleRepository = $em->getRepository("Article");
			
			// look for guides in the repo
			$guides = $articleRepository->findAllByCategory($category);
			
			return $this->render("guidelist.html", array(
				"guides" => $guides
			));
		}
		
	}