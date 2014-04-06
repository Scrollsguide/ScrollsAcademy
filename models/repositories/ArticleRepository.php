<?php
	class ArticleRepository extends Repository {
	
		public function getTableName(){
			return "articles";
		}
	
		public function getEntityName(){
			return "Article";
		}
		
	}