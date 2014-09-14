<?php
	class VoteRepository extends Repository {
	
		public function getTableName(){
			return "votes";
		}
	
		public function getEntityName(){
			return "Vote";
		}

		// saves vote to database
		public function persist(Vote $vote){
		}
	}