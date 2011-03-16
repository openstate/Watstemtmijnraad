<?php

class SkinFactory {
	protected $skinsDir = 'images/watstemtmijnraad/skins';
	protected $baseSkin = 'custom_base';
	protected $skinPrefix = 'custom/';

	protected $name = null;
	protected $params = null;
	protected $baseSkinDir = null;

	protected function loadImage($file) {
		if (!$file) throw new Exception();
		$loadFn = 'imagecreatefrom'.pathinfo($file, PATHINFO_EXTENSION);
		if ($loadFn == 'imagecreatefromjpg') $loadFn = 'imagecreatefromjpeg';
		if ($loadFn == 'imagecreatefrom') throw new Exception($file);
		return $loadFn($file);
	}

	protected function saveImage($img, $file, $transparency = false) {
		$saveFn = 'image'.pathinfo($file, PATHINFO_EXTENSION);
		if ($saveFn == 'imagejpg') $saveFn = 'imagejpeg';
		if ($saveFn == 'imagegif' && !imagecolorstotal($img))
			imagetruecolortopalette($img, true, 256);
		if ($transparency) {
			list($x, $y) = explode(',', $transparency);
			imagecolortransparent($img, imagecolorat($img, $x, $y));
		}
		imagesavealpha($img, true);
		$saveFn($img, $file);
	}

	protected function recolor($file, $params) {
		extract($this->params);
		foreach(array('red', 'green', 'blue', 'value', 'background') as $var)
			eval('$'.$var.' = sscanf('.($params[$var] ? $params[$var] : '"000000"').', "%02x%02x%02x");');

		$img = $this->loadImage($file);

		$i = imagecolorstotal($img);
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

		$outFile = str_replace($this->baseSkin, $this->skinPrefix.$this->name, $file);
		if ($params['rename'])
			$outFile = preg_replace('!/[^/]+$!', '/'.$params['rename'], $outFile);
		if ($params['ext'])
			$outFile = preg_replace('/\.'.preg_quote(pathinfo($outFile, PATHINFO_EXTENSION), '/').'$/', '.'.$params['ext'], $outFile);

		$this->saveImage($img, $outFile);
	}

	protected function recolorTruecolor($file, $params) {
		extract($this->params);
		foreach(array('red', 'green', 'blue', 'value', 'background') as $var)
			eval('$'.$var.' = sscanf('.($params[$var] ? $params[$var] : '"000000"').', "%02x%02x%02x");');

		$img = $this->loadImage($file);
		imagealphablending($img, false);

		$sx = imagesx($img); $sy = imagesy($img);
		for ($x = 0; $x < $sx; $x++) for ($y = 0; $y < $sy; $y++) {
			$rgba = imagecolorat($img, $x, $y);
			list($r, $g, $b, $a) = array(($rgba >> 16) & 255, ($rgba >> 8) & 255, $rgba & 255, ($rgba >> 24) & 127);
			$v = min($r, $g, $b); $k = 255 - max($r, $g, $b);
			$r -= $v; $g -= $v; $b -= $v;
			$c = array(
				($red[0] * $r + $green[0] * $g + $blue[0] * $b + $value[0] * $v + $background[0] * $k) / 255,
				($red[1] * $r + $green[1] * $g + $blue[1] * $b + $value[1] * $v + $background[1] * $k) / 255,
				($red[2] * $r + $green[2] * $g + $blue[2] * $b + $value[2] * $v + $background[2] * $k) / 255
			);
			foreach ($c as &$cmp) $cmp = min($cmp, 255); unset($cmp);
			imagesetpixel($img, $x, $y, imagecolorallocatealpha($img, $c[0], $c[1], $c[2], $a));
		}

		$outFile = str_replace($this->baseSkin, $this->skinPrefix.$this->name, $file);
		if ($params['rename'])
			$outFile = preg_replace('!/[^/]+$!', '/'.$params['rename'], $outFile);
		if ($params['ext'])
			$outFile = preg_replace('/\.'.preg_quote(pathinfo($outFile, PATHINFO_EXTENSION), '/').'$/', '.'.$params['ext'], $outFile);

		$this->saveImage($img, $outFile);
	}

	protected function composite($file, $params) {
		$img = null;
		foreach(array_filter(array($params['background'], $file, $params['foreground'])) as $part) {
			if (!file_exists($part)) continue;
			$comp = $this->loadImage($part);
			if (!$img)
				$img = imagecreatetruecolor(imagesx($comp), imagesy($comp));
			imagecopy($img, $comp, 0, 0, 0, 0, imagesx($img), imagesy($img));
		}

		$outFile = str_replace($this->baseSkin, $this->skinPrefix.$this->name, $file);
		if ($params['rename'])
			$outFile = preg_replace('!/[^/]+$!', '/'.$params['rename'], $outFile);
		if ($params['ext'])
			$outFile = preg_replace('/\.'.preg_quote(pathinfo($outFile, PATHINFO_EXTENSION), '/').'$/', '.'.$params['ext'], $outFile);

		$this->saveImage($img, $outFile, @$params['transparency']);
	}

	protected function ajaxloader($file, $params) {
		extract($this->params);
		foreach(array('value', 'background') as $var)
			eval('$'.$var.' = sscanf('.($params[$var] ? $params[$var] : '"000000"').', "%2s%2s%2s");');

		$url = 'http://www.ajaxload.info/cache/'.
			implode('/', $background).'/'.implode('/', $value).'/6-'.(int)isset($params['transparent']).'.gif';
		$outFile = str_replace($this->baseSkin, $this->skinPrefix.$this->name, $file);
		file_put_contents($outFile, file_get_contents($url));
	}

	protected function header($file, $params) {
		extract($this->params);
		foreach ($params as $key => $value)
			$$key = $this->loadImage($value[0] != '/' ? eval('return '.$value.';') : $value);

		$img = imagecreatetruecolor(921, 216);
		imagecopyresampled($img, $background, 0, 0, 0, 0, 921, 216, imagesx($background), imagesy($background));
		imagecopyresampled($img, $fade, 0, 0, 0, 0, 921, 216, 921, 1);
		imagecopy($img, $wave, 0, 130, 0, 0, 921, 216);
		imagecopy($img, $shadow, 619, 49, 0, 0, 252, 142);
		imagecopy($img, $logo, 624, 0, 0, 0, 245, 216);

		$outFile = str_replace($this->baseSkin, $this->skinPrefix.$this->name, $file);
		$this->saveImage($img, $outFile);
	}

	protected function thumb($file, $params) {
		extract($this->params);
		foreach ($params as $key => $value)
			$$key = $this->loadImage($value[0] != '/' ? eval('return '.$value.';') : $value);

		$img = imagecreatetruecolor(1200, 800);
		imagecopy($img, $layer1, 0, 0, 0, 0, 1200, 800);
		imagecopyresampled($img, $background, 140, 0, 0, 0, 920, 216, imagesx($background), imagesy($background));
		imagecopyresampled($img, $fade, 140, 0, 0, 0, 921, 216, 920, 1);
		imagecopy($img, $layer2, 0, 0, 0, 0, 1200, 800);
		imagecopy($img, $layer3, 0, 0, 0, 0, 1200, 800);
		imagecopy($img, $logo, 764, 0, 0, 0, 245, 216);

		$thumb = imagecreatetruecolor(300, 200);
		imagecopyresampled($thumb, $img, 0, 0, 0, 0, 300, 200, 1200, 800);

		$outFile = str_replace($this->baseSkin, $this->skinPrefix.$this->name, $file);
		$this->saveImage($thumb, $outFile);
	}

	protected function stylesheet_callback($match) {
		extract($this->params);
		return eval('return '.$match[1].';');
	}

	protected function stylesheet($file, $params) {
		file_put_contents(str_replace($this->baseSkin, $this->skinPrefix.$this->name, $file),
			preg_replace_callback('/<<(.+?)>>/', array($this, 'stylesheet_callback'), file_get_contents($file)));
	}

	protected function copy($file, $params) {
		copy($file, str_replace($this->baseSkin, $this->skinPrefix.$this->name, $file));
	}

	protected function copyDirectory($dir, $params) {
		@mkdir(str_replace($this->baseSkin, $this->skinPrefix.$this->name, $dir));
		foreach (scandir($dir) as $file)
			if ($file != '.' && $file != '..')
				copy($dir.'/'.$file, str_replace($this->baseSkin, $this->skinPrefix.$this->name, $dir.'/'.$file));
	}

	protected function clearDirectory($dir, $params) {
		$dir = str_replace($this->baseSkin, $this->skinPrefix.$this->name, $dir);
		foreach (scandir($dir) as $file)
			if ($file != '.' && $file != '..')
				unlink($dir.'/'.$file);
		rmdir($dir);
	}

	protected function recurse($xml, $pwd) {
		@mkdir(str_replace($this->baseSkin, $this->skinPrefix.$this->name, $pwd));
		foreach($xml->children() as $child) {
			$tag = (string) $child->getName();
			if ($tag == 'directory')
				$this->recurse($child, $pwd.'/'.(string) $child['name']);
			else {
				$params = array();
				foreach ($child->children() as $param) {
					$value = (string) $param;
					if ($param['source'] == 'base')
						$value = $this->baseSkinDir.'/'.$value;
					else if ($param['source'] == 'skin')
						$value = str_replace($this->baseSkin, $this->skinPrefix.$this->name, $this->baseSkinDir).'/'.$value;
					else if ($param['source'] == 'basePwd')
						$value = $pwd.'/'.$value;
					else if ($param['source'] == 'skinPwd')
						$value = str_replace($this->baseSkin, $this->skinPrefix.$this->name, $pwd).'/'.$value;
					$params[(string) $param->getName()] = $value;
				}
				$this->$tag($pwd.'/'.(string) $child['name'], $params);
			}
		}
	}

	public function __construct($name, $params) {
		$this->name = $name;
		$this->params = array('name' => $name) + $params;
	}

	public function generate($skinXml = 'skin.xml') {
		$this->baseSkinDir = $_SERVER['DOCUMENT_ROOT'].'/'.$this->skinsDir.'/'.$this->baseSkin.'/';

/*		if (!$this->params['background']) {
			$this->params['background'] =
				str_replace($this->baseSkin, $this->skinPrefix.$this->name, $this->baseSkinDir).'/components/header_background.png';
			$this->generate('background.xml');
		}*/
		
		$xml = simplexml_load_file($this->baseSkinDir.'/'.$skinXml);
		$this->recurse($xml, $this->baseSkinDir);
	}
}

function brighten($color, $value = 0.1) {
	$rgb = sscanf(strtolower($color), '%02x%02x%02x');
	$hsv = rgb2hsv($rgb);
	$hsv[2] = min($hsv[2] + $value, 1);
	$rgb = hsv2rgb($hsv);
	return sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
}

function darken($color, $value = 0.1) {
	$rgb = sscanf(strtolower($color), '%02x%02x%02x');
	$hsv = rgb2hsv($rgb);
	$hsv[2] = max($hsv[2] - $value, 0);
	$rgb = hsv2rgb($hsv);
	return sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
}

function saturize($color, $value = 0.1) {
	$rgb = sscanf(strtolower($color), '%02x%02x%02x');
	$hsv = rgb2hsv($rgb);
	$hsv[1] = min($hsv[1] + $value, 1);
	$rgb = hsv2rgb($hsv);
	return sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
}

function desaturize($color, $value = 0.1) {
	$rgb = sscanf(strtolower($color), '%02x%02x%02x');
	$hsv = rgb2hsv($rgb);
	$hsv[1] = max($hsv[1] - $value, 0);
	$rgb = hsv2rgb($hsv);
	return sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
}

function interpolate($c1, $c2, $factor) {
	$rgb1 = sscanf(strtolower($c1), '%02x%02x%02x');
	$rgb2 = sscanf(strtolower($c2), '%02x%02x%02x');
	$rgb = array_map(create_function('$x,$y', 'return $x * '.$factor.' + $y * '.(1-$factor).';'), $rgb1, $rgb2);
	return sprintf('%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
}

function highlight($color, $value = 0.1) {
	$rgb = sscanf(strtolower($color), '%02x%02x%02x');
	$hsv = rgb2hsv($rgb);
	return $hsv[2] >= 0.5 ? darken($color, $value) : brighten($color, $value);
}

function coalesce($x, $y) {
	return $x ? $x : $y;
}

function width($file) {
	if (!$file) return 0;
	$file = $_SERVER['DOCUMENT_ROOT'].$file;
	if (!file_exists($file)) return 0;
	$loadFn = 'imagecreatefrom'.pathinfo($file, PATHINFO_EXTENSION);
	if ($loadFn == 'imagecreatefromjpg') $loadFn = 'imagecreatefromjpeg';
	if ($loadFn == 'imagecreatefrom') return 0;
	$img = $loadFn($file);
	return imagesx($img);
}

function rgb2hsv($rgb) {
	$max = max($rgb); $min = min($rgb);
	return array(
		(int) ($max == $min ? 0 :
			($max == $rgb[0] ? real_modulus(60 * ($rgb[1] - $rgb[2]) / ($max - $min), 360) :
			($max == $rgb[1] ? 60 * ($rgb[2] - $rgb[0]) / ($max - $min) + 120 :
								60 * ($rgb[0] - $rgb[1]) / ($max - $min) + 240))),
		$max == 0 ? 0 : ($max - $min) / $max,
		$max / 255 
	);
}

function hsv2rgb($hsv) {
	$h = real_modulus((int) ($hsv[0] / 60.0), 6);
	$f = $hsv[0] / 60.0 - (int) ($hsv[0] / 60);
	$p = (int) ($hsv[2] * (1 - $hsv[1]) * 255);
	$q = (int) ($hsv[2] * (1 - $f * $hsv[1]) * 255);
	$t = (int) ($hsv[2] * (1 - (1 - $f) * $hsv[1]) * 255);
	$v = (int) ($hsv[2] * 255);
	switch ($h) {
		case 0: return array($v, $t, $p);
		case 1: return array($q, $v, $p);
		case 2: return array($p, $v, $t);
		case 3: return array($p, $q, $v);
		case 4: return array($t, $p, $v);
		case 5: return array($v, $p, $q);
	}
}

function real_modulus($a, $b) {
	return $a % $b < 0 ? $a % $b + $b : $a % $b;
}

?>