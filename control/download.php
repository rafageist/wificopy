<?php

class page_download {

	static function show(){
		$id = $_GET['id'];
		
		$xfile = model::query("SELECT * FROM xfile WHERE md5(id) = '".$id."';");

		if (isset($xfile[0])) if (md5($xfile[0]['id']) == $id) {
			$path = $xfile[0]['id'];
			$exp = explode("/",$path);
			$size = $xfile[0]['size'];
			$file_name = $exp[count($exp)-1];
			
			if (app::$user['credit']*1>=$xfile[0]['price']*1){

				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=\"$file_name\"");
				header("Content-Length: ".$size);
				header("Pragma: public");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Accept-Ranges: bytes");
				
				set_time_limit(0);
				
				$file = @fopen("D:/WIFI/FILES/$path", "rb");
				
				while(!feof($file))
				{
					print(@fread($file, 1024*8));
					ob_flush();
					flush();
				}

				model::query("UPDATE xfile SET downloads = downloads + 1 WHERE md5(id) = '".$id."';");
				model::query("UPDATE xuser SET credit = credit - ".$xfile[0]['price']." WHERE username = '".app::$user['username']."'");
				model::query("INSERT INTO xuser_activity (id,xuser,activity,xfile,amount) VALUES ('".uniqid("",true)."','".app::$user['username']."','download','".$xfile[0]['id']."',".$xfile[0]['price'].");");		
			} else {
				include "../control/header.php";
				include "../views/not_enought_credit.html";
				include "../control/footer.php";
				
			}
		}
	}
}