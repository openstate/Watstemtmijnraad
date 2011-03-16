<?php 
class downloadPage {
	public function processGet($get) {
    	$filename = 'http://'.$_SERVER['HTTP_HOST'].$get['file'];
        
        header('Content-type: application/txt');
        header('Content-Disposition: attachment; filename="WSMR.srt"');
        readfile($filename);
        die();
    }
}