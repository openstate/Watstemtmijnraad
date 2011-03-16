<?php

/**
 * Creates a preview of the website with entered colors
 */
class previewImagePage {

	public function processGet($get) {
		$colors = array();
		$count = 0;

		foreach($get as $hexColor){
			$colors[$count] = $this->convertToDec($hexColor);
			$count++;
		}

		$imgname = $_SERVER['DOCUMENT_ROOT'].'/images/backoffice/screenshot.gif';
		$img = imagecreatefromgif($imgname);
		$i = imagecolorstotal($img);
		list($red, $green, $blue, $value) = $colors;
		$background = array(0, 0, 0);
		while ($i--) {
			list($r, $g, $b) = array_values(imagecolorsforindex($img, $i));
			$v = min($r, $g, $b); $k = 255 - max($r, $g, $b);
			$r -= $v; $g -= $v; $b -= $v;
			$c = array(
				($red[0] * $r + $green[0] * $g + $blue[0] * $b + $value[0] * $v + $background[0] * $k) / 255,
				($red[1] * $r + $green[1] * $g + $blue[1] * $b + $value[1] * $v + $background[1] * $k) / 255,
				($red[2] * $r + $green[2] * $g + $blue[2] * $b + $value[2] * $v + $background[2] * $k) / 255
			);
			foreach ($c as &$cmp) $cmp = min($cmp, 255); unset($cmp);
			imagecolorset($img, $i, $c[0], $c[1], $c[2]);
		}

		header('Content-type: image/gif');

		imagegif($img);
		imagedestroy($img);

	}

	public function convertToDec($color){
		$decColors = array();
		foreach(str_split($color, 2) as $hexVal) {
			$decColors[] = hexdec($hexVal);
		}
		return $decColors;
	}
}