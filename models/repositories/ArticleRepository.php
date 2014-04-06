<?php
	class ArticleRepository extends Repository {
	
		public function getTableName(){
			return "articles";
		}
	
		public function getEntityName(){
			return "Article";
		}
		
		public function findAllByCategory($categoryString){
			$sth = $this->getConnection()->prepare("SELECT A.*
						FROM articles A, articlecategories AC, categories C
						WHERE C.name = :category
						AND AC.categoryid = C.id
						AND AC.articleid = A.id");
			$sth->bindParam(":category", $categoryString, PDO::PARAM_STR);
			
			$sth->execute();
			
			return $sth->fetchAll(PDO::FETCH_CLASS, $this->getEntityname());
		}
		
	}