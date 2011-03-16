<?php

require_once('IndexHandler.class.php');
/**
 * Description of rssPageclass
 *
 * @author polichism
 */
class IndexPage extends IndexHandler {

    public function show($smarty) {
        require_once('RaadstukkenFeed.class.php');
	$feed = new RaadstukkenFeed(Dispatcher::inst()->region);
        $feed->echoXML();
    }
    //put your code here
}
?>
