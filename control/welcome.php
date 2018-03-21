<?php

class page_welcome{
	
	static function show(){
		
		$r = model::query("SELECT * FROM taxonomy order by title;");
		
		$taxs = array();
		foreach($r as $item){
			$add = false;
			if (!isset($_GET['tax']) && strpos($item['id'], "/") === false) $add = true;
			if (isset($_GET['tax']) && strpos($item['id'], $_GET['tax']) === 0 && count(explode("/",$_GET['tax']))+1 == count(explode("/",$item['id']))) $add = true;
			
			if ($add == true) {
				if ($item['final']=='t')
					$item['url'] = 'index.php?page=search&tax=';
				else 
					$item['url'] = 'index.php?page=welcome&tax=';
				
				$taxs[$item['id']] = $item;				
			}
		}	
		
		$breadcumb = app::getBreadcumb();
		
		include "../views/welcome.html";
		
	}
	
}