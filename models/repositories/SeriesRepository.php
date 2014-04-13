<?php

	class SeriesRepository extends Repository {

		public function getEntityName() {
			return "Series";
		}

		public function getTableName() {
			return "series";
		}

		// saves guide to database
		public function persist(Series $series) {
			$setQuery = "SET title = :title,
				url = :url,
				image = :image";

			$isExistingSeries = ($seriesId = $series->getId()) !== null;
			if ($isExistingSeries) {
				// old series, edit
				$query = "UPDATE " . $this->getTableName() . " " . $setQuery .
					" WHERE id = :id";
			} else {
				// new series, add
				$query = "INSERT INTO " . $this->getTableName() . " " . $setQuery;
			}

			$sth = $this->getConnection()->prepare($query);

			$sth->bindValue(":title", $series->getTitle(), PDO::PARAM_STR);
			$sth->bindValue(":url", $series->getUrl(), PDO::PARAM_STR);
			$sth->bindValue(":image", $series->getImage(), PDO::PARAM_STR);
			if ($isExistingSeries) {
				$sth->bindValue(":id", $seriesId, PDO::PARAM_INT);
			}

			$sth->execute();
		}

	}