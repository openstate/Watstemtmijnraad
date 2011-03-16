<?php



/** Renders correlation bar image */
class CorrelatePage {
	private $party;
	private $region;


	//[FIXME: currently this page is not used. It could be used later with opensocial widget]
	public function processGet($get) {
		if(!isset($get['id']) || !isset($get['ref_id'])) Dispatcher::badRequest();
		header('Content-type: image/png');

		$db = DBs::inst(DBs::SYSTEM);
		$fit = $db->query('SELECT total_fit FROM pol_party_correlations WHERE party_1 = %i AND party_2 = %i', $get['id'], $get['ref_id'])->fetchCell();

		$wd = isset($get['wd'])? intval($get['wd']): 100;
		$hg = isset($get['hg'])? intval($get['hg']): 15;
		$img = imagecreatetruecolor($wd, $hg) or die("Can't initialize image: {$wd} x {$hg}");


		if($fit !== false && $fit !== null && $fit >= 0) {
			//clear image
			$color_back = imagecolorallocate($img, 240, 240, 240);
			imagefilledrectangle($img, 0, 0, $wd, $hg, $color_back);

			//draw bar
			$color_bar =  imagecolorallocate($img, 47, 255, 56);
			imagefilledrectangle($img, 0, 0, $wd * $fit, $hg, $color_bar);
		} else { //record doesn't exist or there was no data to compute fitness
			$color = imagecolorallocate($img, 254, 255, 159);
			imagefilledrectangle($img, 0, 0, $wd, $hg, $color);
		}

		imagepng($img);
		imagedestroy($img);
		exit(0);
	}
}

?>
