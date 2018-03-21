<?php

class page_details{
	
	static function show(){
		if (isset($_GET['id'])){
			$id = $_GET['id'];
			$id = str_replace("'","",$id);
			$xfile = model::getFile($id,true);
			//$xfile = model::query("SELECT * FROM xfile WHERE md5(id) = '$id';");	
			//if (isset($xfile[0])){
				//$xfile = $xfile[0];
				$breadcumb = app::getBreadcumb($xfile['tax']);
				include "../views/details.html";
			//}
		}
	}
	
}