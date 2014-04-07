<?php
	class GuideRepository extends Repository {
	
		public function getTableName(){
			return "guides";
		}
	
		public function getEntityName(){
			return "Guide";
		}
		
		public function findAllByCategory($categoryString){
			$sth = $this->getConnection()->prepare("SELECT A.*
						FROM guides A, guidecategories AC, categories C
						WHERE C.name = :category
						AND AC.categoryid = C.id
						AND AC.guideid = A.id");
			$sth->bindParam(":category", $categoryString, PDO::PARAM_STR);
			
			$sth->execute();
			
			return $sth->fetchAll(PDO::FETCH_CLASS, $this->getEntityname());
		}

		public function findRandomByCategory($categoryString) {
			$all = $this->findAllByCategory($categoryString);
			if (empty($all)) { return false; }
			return $all[array_rand($all)];
		}
		
	}