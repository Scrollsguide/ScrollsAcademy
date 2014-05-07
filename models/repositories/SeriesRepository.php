<?php

	class SeriesRepository extends Repository {

		public function getEntityName() {
			return "Series";
		}

		public function getTableName() {
			return "series";
		}

		public function findSeriesByGuide(Guide $guide){
			$sth = $this->getConnection()->prepare("SELECT S.*
						FROM series S, seriesguides G
						WHERE G.guideid = :id
						AND S.id = G.seriesid
						ORDER BY S.title ASC");

			$sth->bindValue(":id", $guide->getId(), PDO::PARAM_INT);

			$sth->execute();

			$series = $sth->fetchAll(PDO::FETCH_CLASS, $this->getEntityname());

			return $series;
		}

		// saves guide to database
		public function persist(Series $series) {
			$setQuery = "SET title = :title,
				url = :url,
				image = :image,
				banner = :banner,
				summary = :summary";

			$isExistingSeries = ($seriesId = $series->getId()) !== 0;
			if ($isExistingSeries) {
				// old series, edit
				$query = "UPDATE " . $this->getTableName() . " " . $setQuery .
					" WHERE id = :id";

				// make sure to remove guides
				$sth = $this->getConnection()->prepare("DELETE FROM seriesguides WHERE seriesid = :id");
				$sth->bindValue(":id", $seriesId, PDO::PARAM_INT);

				$sth->execute();
			} else {
				// new series, add
				$query = "INSERT INTO " . $this->getTableName() . " " . $setQuery;
			}

			$sth = $this->getConnection()->prepare($query);

			$sth->bindValue(":title", $series->getTitle(), PDO::PARAM_STR);
			$sth->bindValue(":url", $series->getUrl(), PDO::PARAM_STR);
			$sth->bindValue(":image", $series->getImage(), PDO::PARAM_STR);
			$sth->bindValue(":banner", $series->getBanner(), PDO::PARAM_STR);
			$sth->bindValue(":summary", $series->getSummary(), PDO::PARAM_STR);

			if ($isExistingSeries) {
				$sth->bindValue(":id", $seriesId, PDO::PARAM_INT);
			}

			$sth->execute();

			// now update guides
			if (!$isExistingSeries) {
				$seriesId = $this->getConnection()->lastInsertId();
			}

			// start batch transaction
			$this->getConnection()->beginTransaction();

			// watch out! Not at all consistent with a regular series request
			// since getGuides() returns a list of ids here, not strings
			$index = 0;
			foreach ($series->getGuides() as $guide) {
				$index++;
				$sth = $this->getConnection()->prepare("INSERT INTO seriesguides
					SET seriesid = :id, guideid = :guideid, `order` = :order");

				$sth->bindValue(":id", $seriesId, PDO::PARAM_INT);
				$sth->bindValue(":guideid", $guide, PDO::PARAM_INT);
				$sth->bindValue(":order", $index, PDO::PARAM_INT);

				$sth->execute();
			}

			// finish inserting guides
			$this->getConnection()->commit();
		}
	}