<?php
	class Guide {
	
		private $id;
	
		private $title;

		private $summary;

		private $content;
		
		private $url;
		
		private $image;
		
		private $categories = array();
		
		public function __construct(){
			
		}
		
		public function getId(){
			return $this->id;
		}

		public function setId($id){
			$this->id = $id;
		}

		public function setUrl($url){
			$this->url = $url;
		}

		public function getUrl(){
			return $this->url;
		}
		
		public function getTitle(){
			return $this->title;
		}
		
		public function setTitle($title){
			$this->title = $title;
		}

		public function getSummary() {
			return $this->summary;
		}

		public function setSummary($summary) {
			$this->summary = $summary;
		}
		
		public function getContent(){
			return $this->content;
		}
		
		public function setContent($content){
			$this->content = $content;
		}
		
		public function getImage(){
			return $this->image;
		}
		
		public function setImage($image){
			$this->image = $image;
		}
		
		public function addCategory($category){
			$this->categories[] = $category;
		}
		
		public function getCategories(){
			return $this->categories;
		}
		
	}