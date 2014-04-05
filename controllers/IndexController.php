<?php
	class IndexController extends Controller {
		
		public function indexAction(){
			return $this->render("index.html", array(
				"data" => "twigthing"
			));
		}
		
	}