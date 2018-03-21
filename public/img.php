<?php

include_once "../control/app.php";
include_once "../model/model.php";
require_once ('../lib/getid3/getid3.php');


function array_search_recursive($arr, $key, $subkey = "")
{
	$result = array();

	foreach ($arr as $k => $v) {

		if ($key === $k)
			$result[$subkey . "." . $k] = $v;

		if (is_array($v))
			$result = array_merge($result, array_search_recursive($v, $key, $k));
	}

	return $result;
}

function array_get_first_scalar($arr)
{

	if (is_scalar($arr))
		return $arr;

	if (is_array($arr))
		foreach ($arr as $k => $v) {
			if (is_scalar($v))
				return $v;

			if (is_array($v)) {
				$first = array_get_first_scalar($v);
				if (is_scalar($first) && !is_null($first))
					return $first;
			}
		}

	return null;
}

function array_search_first_scalar($arr, $key)
{
	return array_get_first_scalar(array_search_recursive($arr, $key));
}

function getMusicMetadata($path)
{

	$getID3 = new getID3;
	//echo '[INFO] Analyze '.$path."\n";
	$r = $getID3->analyze($path);

	$picture = array_search_recursive($r, 'picture');
	$image = array_search_recursive($picture, 'data');
	$image = array_get_first_scalar($image);
	$image_mime = array_search_recursive($picture, 'image_mime');
	$image_mime = array_get_first_scalar($image_mime);
	$image_mime = str_replace("image/", "", $image_mime);

	return array(
		'artist' => array_search_first_scalar($r, 'artist'),
		'ambum' => array_search_first_scalar($r, 'album'),
		'title' => array_search_first_scalar($r, 'title'),
		'track' => array_search_first_scalar($r, 'track'),
		'duration' => array_search_first_scalar($r, 'playtime_string'),
		'bitrate' => number_format(array_search_first_scalar($r, 'bitrate') / 1000, 0) . " kbps",
		'sample_rate' => number_format(array_search_first_scalar($r, 'sample_rate') / 1000, 1) . " kHz",
		'image' => $image,
		'image_type' => $image_mime);
}

function resize($path, $value, $prop = 'width')
{

	$info = getimagesize($path);

	$prop_value = ($prop == 'width') ? $info[0] : $info[1];
	$prop_versus = ($prop == 'width') ? $info[1] : $info[0];

	$pcent = $value / $prop_value;
	$value_versus = $prop_versus * $pcent;

	$image = ($prop == 'width') ? imagecreatetruecolor($value, $value_versus) : imagecreatetruecolor($value_versus, $value);

	$type = strtolower($info['mime']);
	$type = str_replace("image/", "", $type);

	switch ($type) {
		case "jpeg":
			$content = @imagecreatefromjpeg($path);
			break;
		case "gif":
			$content = @imagecreatefromgif($path);
			if (is_resource($content))
				$transparent_color = imagecolortransparent($content);
			break;
		case "png":
			$content = @imagecreatefrompng($path);
			if (is_resource($content))
				$transparent_color = imagecolortransparent($content);
			break;
	}


	switch ($prop) {
		case 'width':
			if (is_resource($content))
				imagecopyresampled($image, $content, 0, 0, 0, 0, $value, $value_versus, $info[0], $info[1]);
			break;
		case 'height':
			if (is_resource($content))
				imagecopyresampled($image, $content, 0, 0, 0, 0, $value_versus, $value, $info[0], $info[1]);
			break;
	}

	ob_start();

	switch ($type) {
		case "jpeg":
			imagejpeg($image);
			break;
		case "gif":
			imagegif($image);
			break;
		case "png":
			$pngquality = floor(($quality - 10) / 10);
			imagecolortransparent($image, $transparent_color);
			imagepng($image, null, $pngquality);
			break;
	}
	$newcontent = ob_get_contents();
	ob_end_clean();

	return $newcontent;
}


function extractIcon($path)
{

	$zip = zip_open($path);

	if (is_integer($zip))
		return false;

	$priority = array(
		0 => 9999,
		"res/drawable/icon.png" => 9,
		"res/drawable/app_icon.png" => 10,
		"res/drawable/ic_app.png" => 11,
		"res/drawable-hdpi/icon.png" => 2.4,
		"res/drawable-xhdpi-v4/app_icon.png" => 1,
		"res/drawable-xhdpi-v4/ic_launcher.png" => 2.2,
		"res/drawable-hdpi/ic_launcher.png" => 3,
		"res/drawable-ldpi/icon.png" => 4,
		"res/drawable-mdpi/icon.png" => 5,
		"res/drawable-hdpi-v2/ic_launcher.png" => 6,
		"res/drawable-hdpi-v3/ic_launcher.png" => 7,
		"res/drawable-hdpi-v4/ic_launcher.png" => 8);

	$ya = 0;
	$buf = '';
	if ($zip) {
		while ($zip_entry = zip_read($zip)) {
			$name = zip_entry_name($zip_entry);

			if (isset($priority[$name]) || strpos($name, "_icon.png") !== false) {
				if (zip_entry_open($zip, $zip_entry, "r")) {
					$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

					if (isset($priority[$name])) {
						if (isset($priority[$name]))
							if ($priority[$name] > $priority[$ya]) {
								$ya = $name;
								continue;
							}
					} else
						continue;

					zip_entry_close($zip_entry);
				}
			}
		}
		zip_close($zip);
	}

	if (empty(trim($buf))){
        return file_get_contents("icons/apk.png");	    
	}
	
	return $buf;
	
}

$root = app::$reporoot;
$id = $_GET['id'];

$xfile = model::getFile($id, true);

if (!isset($_GET['type']))
	$_GET['type'] = 'picture';

$t = $_GET['type'];

if ($xfile !== false) {
	if (isset($xfile["{$t}_path"])) {
		$p = $xfile["{$t}_path"];
		if ($p !== false) {
			header("Content-type: image/png");
			if (file_exists($p)) {
				@readfile($p);
				exit();
			}
		} elseif (file_exists(app::getFolder(app::$reporoot . $xfile['id']) . "/folder.picture.png")) {
			header("Content-type: image/png");
			echo file_get_contents(app::getFolder(app::$reporoot . $xfile['id']) . "/folder.picture.png");
			exit();
		}
	}

	$ext = strtolower($xfile['ext']);
	switch ($ext) {
		case 'jpg':
		case 'jpeg':
		case 'bmp':
		case 'png':
		case 'gif':
			if ($xfile['size'] * 1 < 5 * 1024 * 1024) {
				header("Content-type: image/$ext");
				echo resize("{$root}{$xfile['id']}", 300, 'width');
			}
			break;

		case 'mp3':
		case 'wma':
		case 'wav':
			$info = getMusicMetadata("{$root}{$xfile['id']}");
			if (trim($info['image']) !== '') {
				header("Content-type: image/" . $info['image_type']);
				echo $info['image'];
			}
			break;
		case 'apk':
			header("Content-type: image/png");
			echo extractIcon("{$root}{$xfile['id']}");
			break;
		default:
			$p = "./icons/{$xfile['ext']}.png";
			if (file_exists($p)) {
				header("Content-type: image/png");
				echo file_get_contents($p);
			}
			break;
	}

}
