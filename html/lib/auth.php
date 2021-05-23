<?php
	/* auth.php: library for user authentication */
	/* CREATE TABLE account(
		userid INTEGER PRIMARY KEY,
		email TEXT UNIQUE,
		name TEXT,
		salt TEXT,
		password INTEGER,
		admin INTEGER
	); */
	
	include_once('./db.inc.php');
	
	function auth($req_admin = false){
		// resume existing session
		if(!empty($_SESSION['auth']) && $req_admin == false)
			return $_SESSION['auth']['em'];
		if(!empty($_COOKIE['auth'])){
			if($t = json_decode(stripslashes($_COOKIE['auth']), true)){
				// input validation and sanitization
				if(empty($t['em']) || !preg_match("/^[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})$/", $t['em']))
					return false;
				$email = filter_var($t['em'], FILTER_SANITIZE_EMAIL);
				
				// return false if the cookie is expired
				if(time() > $t['exp'])
					return false;
				
				// obtain user account information given an email from cookie
				$db = ierg4210_DB();
				if($req_admin)
					$sql = "SELECT * FROM account WHERE email = (?) and admin = 1;";
				else $sql = "SELECT * FROM account WHERE email = (?);";
				$q = $db -> prepare($sql);
				$q -> bindParam(1, $email);
				$q -> execute();
				if($res = $q -> fetch()){
					$realk = hash_hmac('sha256', $t['exp'].$res['password'], $res['salt']);
					// start a new session if the user is authenticated
					if($realk == $t['k']){
						$_SESSION['auth'] = $t;
						return $t['em'];
					}
				}
			}
		}
		return false;
	}
	
	function get_username_by_email($email){
		// input validation and sanitization
		if(empty($email) || !preg_match("/^[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})$/", $email))
			return 'Guest';
		else{
			$email = filter_var($email, FILTER_SANITIZE_EMAIL);
			
			// obtain username given an email
			$db = ierg4210_DB();
			$sql = "SELECT * FROM account WHERE email=(?);";
			$q = $db -> prepare($sql);
			$q -> bindParam(1, $email);
			$q -> execute();
			if($res = $q -> fetch()){
				return $res['name'];
			}else return 'Guest';
		}
	}
?>