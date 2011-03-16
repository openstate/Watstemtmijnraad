<?php
require_once('FeedGeneratorBase.class.php');
//require_once('Classification.class.php');

class RaadstukkenFeed extends FeedGeneratorBase {
	//private $classid;
	public function __construct($region = null) {
		//$this->classid = Classification::getClassificationId('news');
		parent::__construct('rss.xml');
		$this->region = $region;
	}

	public function buildFeed() {
		$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/';
		$this->feed->title = 'Wat stemt mijn raad raadstukken';
		$this->feed->description = 'Raadstukken';
		$this->feed->link = $baseUrl . 'rss/';
		$this->feed->syndicationURL = $baseUrl . 'rss';

                $db = DBs::inst(DBs::SYSTEM);
                $results = $db->query('SELECT id, title, summary, vote_date FROM rs_raadsstukken WHERE %l show = \'1\' ORDER BY vote_date DESC LIMIT 25', $this->region ? 'region = '.(int) $this->region->id.' AND' : '')->fetchAllRows();

                foreach($results as $result) {
                        $item = new FeedItem();
                        $item->title = html_entity_decode($result['title'], ENT_QUOTES, "UTF-8");
                        $item->link = $baseUrl . 'raadsstukken/raadsstuk/' . $result['id'];
                        $item->description = html_entity_decode($result['summary'], ENT_QUOTES, "UTF-8");
                        $item->descriptionTruncSize = 500;
                        $item->descriptionHtmlSyndicated = true;
			$date = new DateTime($result['vote_date']);
			$item->date = $date->format('Y-m-d\TH:i:s') . '+0100';
                        $item->source = $baseUrl;

                        $this->feed->addItem($item);
                }
	}
}
?>
