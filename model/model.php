<?php

class model
{

	static $db = null;

	static function connect()
	{
		if (is_null(self::$db))
			self::$db = pg_connect("host=localhost port=5432 dbname=copypoint user=rafa password=creandohorarios");
	}

	static function query($sql)
	{
		self::connect();
		return pg_fetch_all(pg_query(self::$db, $sql));

	}

	static function translate($word)
	{
		$dic = array(
			'artist' => 'artista',
			'title' => 't&iacute;tulo',
			'track' => 'pista',
			'sample_rate' => 'frecuencia muestreo',
			'bitrate' => 'bits de muestreo',
			'duration' => 'duraci&oacute;n',
			'genre' => 'g&eacute;nero',
			'publisher' => 'publicador');

		if (isset($dic[$word]))
			return $dic[$word];

		return $word;
	}

	static function getFile($id, $md5 = false)
	{
		self::connect();
		$xfile = array();
		if ($md5 == true)
			$xfile = self::query("SELECT * FROM xfile WHERE md5(id) = '" . $id . "';");
		else
			$xfile = self::query("SELECT * FROM xfile WHERE id = '" . $id . "';");

		if (isset($xfile[0]))
			if (($xfile[0]['id'] == $id && $md5 == false) || (md5($xfile[0]['id'] == $id) && $md5 == true)) {
				$xfile = $xfile[0];
				$xfile['properties'] = array();

				// parse ini file
				if (file_exists(app::$reporoot . $xfile['id'] . '.ini')) {
					$properties = parse_ini_file(app::$reporoot . $xfile['id'] . '.ini', true, INI_SCANNER_RAW);
					$xfile['properties'] = $properties;
				}

				$xfile['picture_path'] = false;
				$xfile['icon_path'] = false;

				if (file_exists(app::$reporoot . $xfile['id'] . '.picture.png'))
					$xfile['picture_path'] = app::$reporoot . $xfile['id'] . '.picture.png';
				elseif (isset($properties['picture'])) {
					$xfile['picture_path'] = $properties['picture'];
					$xfile['picture_path'] = str_replace('{$reporoot}', app::$reporoot, $xfile['picture_path']);
					$xfile['picture_path'] = str_replace('{$current_folder}', app::$reporoot . '/' . app::getFolder($xfile['id']), $xfile['picture_path']);
					$xfile['picture_path'] = str_replace("//", "/", $xfile['picture_path']);
				}

				if (file_exists(app::$reporoot . $xfile['id'] . '.icon.png'))
					$xfile['icon_path'] = app::$reporoot . $xfile['id'] . '.icon.png';
				elseif (isset($properties['icon'])) {
					$xfile['icon_path'] = $properties['icon'];
					$xfile['icon_path'] = str_replace('{$reporoot}', app::$reporoot, $xfile['icon_path']);
					$xfile['icon_path'] = str_replace('{$current_folder}', app::$reporoot . '/' . app::getFolder($xfile['id']), $xfile['icon_path']);
					$xfile['icon_path'] = str_replace("//", "/", $xfile['icon_path']);
				}

				if ($xfile['icon_path'] == false)
					$xfile['icon_path'] = $xfile['picture_path'];
				elseif ($xfile['picture_path'] == false)
					$xfile['picture_path'] = $xfile['icon_path'];


				$ext = strtolower($xfile['ext']);

				switch ($ext) {
					case 'apk':
						include "../lib/ApkParser/SeekableStream.php";
						include "../lib/ApkParser/ResourcesParser.php";
						include "../lib/ApkParser/Stream.php";
						include "../lib/ApkParser/Xml.php";
						include "../lib/ApkParser/XmlParser.php";
						include "../lib/ApkParser/Manifest.php";
						include "../lib/ApkParser/Archive.php";
						include "../lib/ApkParser/Config.php";
						include "../lib/ApkParser/Parser.php";
                        
						try {
							$parser = new \ApkParser\Parser(app::$reporoot . "/" . $xfile['id']);
							$manifest = $parser->getManifest();

							if (!isset($xfile['version'])) {
								$version = $manifest->getVersionName();
								if ($version == null) {
									$version = '';
								}
								$xfile['version'] = $version;
							}
						}
						catch (exception $e) {
						}
						break;
				}

				return $xfile;
			}

		return false;
	}
}
