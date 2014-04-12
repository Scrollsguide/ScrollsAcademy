<?php
	class HomepageRepository extends Repository {
	
		public function getTableName(){
			return "homepages";
		}
	
		public function getEntityName(){
			return "Homepage";
		}

		public function findHomepageBlocks($homepage) {
			$sth = $this->getConnection()->prepare("SELECT *
						FROM homepageblocks
						WHERE homepageid = :homepageId 
						ORDER BY id");
			$sth->bindValue(":homepageId", $homepage->getId(), PDO::PARAM_INT);

			$sth->execute();
			
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		}

		// saves homepage to database
		public function persist(Homepage $homepage){
			$isExistingHomepage = ($homepageId = $homepage->getId()) !== null;
			if ($isExistingHomepage){
				//remove old blocks
				$sth = $this->getConnection()->prepare("DELETE FROM homepageblocks WHERE homepageid = :id");
				$sth->bindValue(":id", $homepageId, PDO::PARAM_INT);

				$sth->execute();
			}

			// update blocks
			$this->getConnection()->beginTransaction();
			foreach ($homepage->getBlocks() as $block){
				$sth = $this->getConnection()->prepare("INSERT INTO homepageblocks
					SET homepageid = :id, layout = :layout, guideids = :guideids");
				$sth->bindValue(":id", $homepageId, PDO::PARAM_INT);
				$sth->bindValue(":layout", $block['layout'], PDO::PARAM_STR);
				$sth->bindValue(":guideids", $block['guideids'], PDO::PARAM_STR);

				$sth->execute();
			}
			// finish inserting categories
			$this->getConnection()->commit();
		}
	}