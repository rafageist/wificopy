<?php

class page_search{
	
	static function show(){
		
		$results = false;
		$words = array();
		$tax = "";
		
		if (isset($_GET['tax']))
			$tax = " AND strpos(tax,'{$_GET['tax']}') > 0";
		
		if (isset($_GET['search'])){
			$phrase = $_GET['search'];			
			$phrase = str_replace("'", "", $phrase);
			$phrase = trim($phrase);
			if (strlen($phrase) > 1){
				$phrase = explode(" ", $phrase);
				$where = 'false ';
				
				foreach($phrase as $word){
					if (trim($word)!==''){
						$words[] = $word;
						$where .= "or id || ' ' || title || ' ' || \"desc\" ~* '$word'";
					}
				}
			
				$results = model::query("SELECT * FROM xfile WHERE ($where) $tax LIMIT 20;");
			}
		} else 
			$results = model::query("SELECT * FROM xfile WHERE TRUE $tax ORDER BY random() LIMIT 20;");
			
		$taxs = model::query("SELECT * FROM taxonomy;");
		
		foreach($taxs as $key => $tax)
			$taxs[$tax['id']] = $tax;
	
		$breadcumb = app::getBreadcumb();
		
		include "../views/search.html";
		
	}
	
}