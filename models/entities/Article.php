<?php
	class Article {
	
		private $title;
		
		private $content;
		
		public function __construct(){
			
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
		
	}