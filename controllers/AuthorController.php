<?php

	class AuthorController extends BaseController {

		public function viewGuidesAction($author){
			// set up entity and repository
			$em = $this->getApp()->get("EntityManager");
			$guideRepository = $em->getRepository("Guide");

			// look for guide in the repo
			if (($guides = $guideRepository->findAllByAuthor($author)) !== false) {
				return $this->render("guidelist.html", array(
					"guides" => $guides,
					"category" => "Guides by " . $author,
					"title"	=> "Guides by " . $author
				));
			} else {
				return $this->p404;
			}

		}

	}