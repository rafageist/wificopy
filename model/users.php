<?php

class users extends model{
	
	static function getUser($user){
		/*
		$file = '../data/users/'.$user;
		if (file_exists($file)){
			$user = self::completeUser(unserialize(file_get_contents($file)));
		} else return false;
		return $user;*/
		
		$user = str_replace("'","",$user);
		$u = self::query("SELECT * FROM xuser WHERE username = '$user';");
		if (isset($u[0])) if ($u[0]['username'] == $user) return $u[0];
		return false;
	}
	
	static function setUser($user){
		$file = '../data/users/'.$user['user'];
		file_put_contents($file, serialize(self::completeUser($user)));
	}
	
	static function getAuth($user, $pass){
		$user = self::getUser($user);
		if ($user == false) return false;
		if ($user['userpass']!==$pass) return false;
		return true;
	}

	static function completeUser($user){
		if (!isset($user['credit'])) $user['credit'] = 0;
		return $user;
	}
	
}
