<?php

include_once  "../model/users.php";

class page_auth{
	
	static function action_login(){
			$user = $_POST['user'];
			$pass = $_POST['pass'];
			
			$login = users::getAuth($user, $pass);
			
			if ($login){
				$_SESSION['user'] = $user;
				header('location: index.php');
			}
			else
				self::show(true);
	}
	
	static function action_logout(){
		unset($_SESSION['user']);
		session_destroy();
		app::$user = null;
		unset($_GET['action']);
		self::show();
	}
	
	static function show($error = false){
		include "../views/auth.html";
	}
	
}


