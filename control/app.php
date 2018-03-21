<?php

include "../model/model.php";

class app {
	
	static $user = null;
	static $reporoot = 'D:/WIFI/FILES/';
	
	static function verifyAuth(){
		
		if (!isset($_SESSION['user']))
			return false;
		
		return true;
		
	}
	
	static function friendlySize($size){
		if ($size < 1024) return '<b>'.$size."</b><small>B</small>";
		if ($size >= 1024 && $size <1024*1024) return '<b>'.number_format($size/1024,2)."</b><small>KB</small>";
		if ($size >= 1024*1024 && $size <1024*1024*1024) return '<b>'.number_format($size/(1024*1024),2)."</b><small>MB</small>";
		return '<b>'.number_format($size/(1024*1024*1024),2)."</b><small>GB</small>";
	}
	
	static function isPublicPage(){
	
		if (isset($_GET['page'])){
			if (array_search($_GET['page'],array('search','details','welcome'))!==false){
				return true;
			}
		} else return true;
		
		return false;
	}
	
	static function showPage($page){
		if ($page !== "download") 
			include "../control/header.php";
		
		include "../control/$page.php";
		
		if (isset($_GET['action'])){
			$methods = get_class_methods('page_'.$page);
			//var_dump($methods);
			if (array_search('action_'.$_GET['action'],$methods)!==false)
				eval('page_'.$page.'::action_'.$_GET['action'].'();');
			else
				eval('page_'.$page.'::show();');	
		} else {
			eval('page_'.$page.'::show();');	
		}
		
		
		
		if ($page !== "download") include "../control/footer.php";
	}
	
	static function getFolder($file){
		$parts = explode("/",$file);
		unset($parts[count($parts)-1]);
		return implode("/",$parts);
	}
	
	static function getBreadcumb($tax = null){
		if (isset($_GET['tax']))
			$tax = $_GET['tax'];
		
		$r = model::query("SELECT * FROM taxonomy order by title;");
		
		$alltaxs = array();
		foreach($r as $item) {
			
			if ($item['final']=='t')
					$item['url'] = 'index.php?page=search&tax=';
				else 
					$item['url'] = 'index.php?page=welcome&tax=';
				
			$alltaxs[$item['id']] = $item;	
		}
		
		
		$breadcumb = array(array(
			'url' => 'index.php',
			'title' => 'Inicio'
		));
		
		if (!is_null($tax)){
			if (isset($alltaxs[$tax])){
				
				$parts = explode("/",$tax);
				
				$path = '';
				foreach($parts as $part){
					$newpath = $path."/".$part;
					if ($newpath[0]=='/')
						$newpath = substr($newpath,1);
					
					if (isset($alltaxs[$newpath]))
						$breadcumb[] = array(
							'url' => $alltaxs[$newpath]['url'].$newpath,
							'title' => $alltaxs[$newpath]['title']
						);
						
					$path = $newpath;
				}
			}
		}
		
		return $breadcumb;
	}
	
	static function Run(){
		// echo serialize(array('user'=>'rafa','pass'=>'creandohorarios','credit'=>1000));
		if (self::verifyAuth() || self::isPublicPage()){
			include_once "../model/users.php";
			
			if (self::verifyAuth()) self::$user = users::getUser($_SESSION['user']);
			
			if (!isset($_GET['page']))
				$_GET['page'] = 'welcome';
			
			self::showPage($_GET['page']);
			
		} else {
			self::showPage('auth');
		}
	}
	
}