<?php
require_once('FeedCreator.class.php');

abstract class FeedGeneratorBase {
	protected $feed;
	private $cache;
	private $timeout;

	public function __construct($filename, $timeout = 600) {
		$this->cache = realpath($_SERVER['DOCUMENT_ROOT'] .
			'/../classes/rssfeed/rsscache') . '/' . $filename;
		$this->feed = new UniversalFeedCreator();
		$this->timeout = $timeout;

		$this->feed->descriptionTruncSize = 500;
		$this->feed->descriptionHtmlSyndicated = true;

	}

	public function echoXML() {
		// use cache if not older than timeout
		$this->feed->useCached($this->cache, $this->timeout);

		$this->buildFeed();

		echo $this->feed->saveFeed('RSS1.0', $this->cache);
	}

	abstract function buildFeed();

	public function addDefaultImage() {
		$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/';
		$image = new FeedImage();
		$image->title = 'Wat stemt mijn raad';
		$image->url = $baseUrl . 'images/logo-mvo.jpg';
		$image->link = $baseUrl;
		$this->feed->image = $image;
	}
}
?>
