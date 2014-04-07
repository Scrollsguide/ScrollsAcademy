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
			
			$guides = $sth->fetchAll(PDO::FETCH_CLASS, $this->getEntityname());
			foreach ($guides as $guide) {
				$categories = $this->findGuideCategories($guide);
				foreach ($categories as $category){
					$guide->addCategory($category);
				}
			}
			return $guides;
		}

		//this should use a minimal query since it only is looking for one guide
		public function findRandomByCategory($categoryString) {
			$all = $this->findAllByCategory($categoryString);
			
			if (empty($all)){
				return false;
			}
			
			return $all[array_rand($all)];
		}
		

		public function findGuideCategories(Guide $guide) {
			$sth = $this->getConnection()->prepare("SELECT C.name
						FROM categories C
						JOIN guidecategories A
						ON C.id = A.categoryid
						WHERE A.guideid = :guideId");
			$sth->bindValue(":guideId", $guide->getId(), PDO::PARAM_INT);

			$sth->execute();
			
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		}
	}