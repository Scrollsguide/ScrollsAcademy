<?php

	class GuideRepository extends Repository {

		public function getTableName() {
			return "guides";
		}

		public function getEntityName() {
			return "Guide";
		}

		public function findAllByCategory($categoryString) {
			$sth = $this->getConnection()->prepare("SELECT A.*
						FROM guides A, guidecategories AC, categories C
						WHERE C.name = :category
						AND AC.categoryid = C.id
						AND AC.guideid = A.id");
			$sth->bindParam(":category", $categoryString, PDO::PARAM_STR);

			$sth->execute();

			$guides = $sth->fetchAll(PDO::FETCH_CLASS, $this->getEntityname());
			foreach ($guides as $guide) {
				$this->findGuideCategories($guide);
			}

			return $guides;
		}

		public function findAllBySeries(Series $series) {
			$sth = $this->getConnection()->prepare("SELECT A.*
						FROM guides A, seriesguides B
						WHERE B.seriesid = :id
						AND A.id = B.guideid
						ORDER BY B.order ASC");
			$sth->bindValue(":id", $series->getId(), PDO::PARAM_INT);

			$sth->execute();

			$guides = $sth->fetchAll(PDO::FETCH_CLASS, $this->getEntityname());
			foreach ($guides as $guide) {
				$this->findGuideCategories($guide);
			}

			return $guides;
		}

		public function findRecentGuides($limit = 3){
			$sth = $this->getConnection()->prepare("SELECT *
						FROM guides
						ORDER BY id DESC
						LIMIT " . $limit);
			$sth->execute();

			$guides = $sth->fetchAll(PDO::FETCH_CLASS, $this->getEntityName());
			foreach ($guides as $guide) {
				$this->findGuideCategories($guide);
			}

			return $guides;
		}

		//this should use a minimal query since it only is looking for one guide
		public function findRandomByCategory($categoryString) {
			$all = $this->findAllByCategory($categoryString);

			if (empty($all)) {
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

			$categories = $sth->fetchAll(PDO::FETCH_ASSOC);
			foreach ($categories as $category){
				$guide->addCategory($category);
			}

			return $categories;
		}

		public function findAllCategories() {
			$sth = $this->getConnection()->prepare("SELECT *
				FROM categories");

			$sth->execute();

			return $sth->fetchAll(PDO::FETCH_ASSOC);
		}

		// saves guide to database
		public function persist(Guide $guide) {
			$setQuery = "SET title = :title,
				summary = :summary,
				synopsis = :synopsis,
				content = :content,
				markdown = :markdown,
				url = :url,
				author = :author,
				image = :image,
				banner = :banner,
				`status` = :status,
				video = :video,
				discussion = :discussion";

			$isExistingGuide = ($guideId = $guide->getId()) !== 0;
			if ($isExistingGuide) {
				// old guide, edit
				$query = "UPDATE " . $this->getTableName() . " " . $setQuery .
					" WHERE id = :id";

				// make sure to remove categories
				$sth = $this->getConnection()->prepare("DELETE FROM guidecategories WHERE guideid = :id");
				$sth->bindValue(":id", $guideId, PDO::PARAM_INT);

				$sth->execute();
			} else {
				// new guide, add
				$setQuery .= ", date = UNIX_TIMESTAMP()"; // temporary solution
				$query = "INSERT INTO " . $this->getTableName() . " " . $setQuery;
			}

			$sth = $this->getConnection()->prepare($query);

			$sth->bindValue(":title", $guide->getTitle(), PDO::PARAM_STR);
			$sth->bindValue(":summary", $guide->getSummary(), PDO::PARAM_STR);
			$sth->bindValue(":synopsis", $guide->getSynopsis(), PDO::PARAM_STR);
			$sth->bindValue(":content", $guide->getContent(), PDO::PARAM_STR);
			$sth->bindValue(":markdown", $guide->getMarkdown(), PDO::PARAM_STR);
			$sth->bindValue(":author", $guide->getAuthor(), PDO::PARAM_STR);
			$sth->bindValue(":url", $guide->getUrl(), PDO::PARAM_STR);
			$sth->bindValue(":image", $guide->getImage(), PDO::PARAM_STR);
			$sth->bindValue(":banner", $guide->getBanner(), PDO::PARAM_STR);
			$sth->bindValue(":status", $guide->getStatus(), PDO::PARAM_INT);
			$sth->bindValue(":video", $guide->getVideo(), PDO::PARAM_STR);
			$sth->bindValue(":discussion", $guide->getDiscussion(), PDO::PARAM_STR);

			if ($isExistingGuide){
				$sth->bindValue(":id", $guideId, PDO::PARAM_INT);
			}

			$sth->execute();

			// now update categories
			if (!$isExistingGuide) {
				$guideId = $this->getConnection()->lastInsertId();
			}

			// watch out! Not at all consistent with a regular guide request
			// since getCategories() returns a list of ids here, not strings
			$this->getConnection()->beginTransaction();
			foreach ($guide->getCategories() as $category) {
				$sth = $this->getConnection()->prepare("INSERT INTO guidecategories
					SET guideid = :id, categoryid = :catid");
				$sth->bindValue(":id", $guideId, PDO::PARAM_INT);
				$sth->bindValue(":catid", $category, PDO::PARAM_INT);

				$sth->execute();
			}
			// finish inserting categories
			$this->getConnection()->commit();
		}

	}