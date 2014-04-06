<?php
	class HtmlResponse extends Response implements ContentResponse {
		
		private $content = "";
		
		public function setContent($content){
			$this->content = $content;
		}
		
		public function getContent(){
			return $this->content;
		}
		
	}