<?php
	class Guide {
	
		private $id;
	
		private $title;
		
		private $content;
		
		private $url;
		
		private $image;
		
		private $categories = array();
		
		public function __construct(){
			
		}
		
		public function getId(){
			return $this->id;
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