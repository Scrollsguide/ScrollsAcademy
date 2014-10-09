<?php

	class Guide {

		private $id;

		private $title;

		private $summary;

		private $synopsis;

		private $markdown;

		private $content;

		private $date;

		private $url;

		private $author;

		private $image;

		private $banner;

		private $status;

		private $categories = array();

		private $video; //raw embed code (maybe parse it later)

		private $discussion; // url to discussion website

		private $reviewed; // is guide reviewed or not

		public function __construct() {

		}

		public function getId() {
			return (int)$this->id;
		}

		public function setId($id) {
			$this->id = $id;
		}

		public function getUrl() {
			return $this->url;
		}

		public function setUrl($url) {
			$this->url = $url;
		}

		public function getTitle() {
			return $this->title;
		}

		public function setTitle($title) {
			$this->title = $title;
		}

		public function getSummary() {
			return $this->summary;
		}

		public function setSummary($summary) {
			$this->summary = $summary;
		}

		public function getSynopsis() {
			if ($this->synopsis === "") {
				// return first 1000 chars from content without html tags
				return substr(strip_tags($this->getContent()), 0, 400);
			}

			return $this->synopsis;
		}

		public function setSynopsis($synopsis) {
			$this->synopsis = $synopsis;
		}

		public function getDate() {
			return (int)$this->date;
		}

		public function setDate($date) {
			$this->date = $date;
		}

		public function getMarkdown() {
			return $this->markdown;
		}

		public function setMarkdown($markdown) {
			$this->markdown = $markdown;
		}

		public function getContent() {
			return $this->content;
		}

		public function setContent($content) {
			$this->content = $content;
		}

		public function getImage() {
			return $this->image;
		}

		public function setImage($image) {
			$this->image = $image;
		}

		public function getBanner() {
			return $this->banner;
		}

		public function setBanner($banner) {
			$this->banner = $banner;
		}

		public function getAuthor() {
			return $this->author;
		}

		public function setAuthor($author) {
			$this->author = $author;
		}

		public function getCategories() {
			return $this->categories;
		}

		public function addCategory($category) {
			$this->categories[] = $category;
		}

		public function getStatus() {
			return (int)$this->status;
		}

		public function setStatus($status) {
			$this->status = $status;
		}

		public function getVideo() {
			return $this->video;
		}

		public function setVideo($video) {
			$this->video = $video;
		}

		public function getDiscussion() {
			return $this->discussion;
		}

		public function setDiscussion($discussion) {
			$this->discussion = $discussion;
		}

		public function getReviewed() {
			return (int)$this->reviewed;
		}

		public function setReviewed($reviewed) {
			$this->reviewed = $reviewed;
		}

	}