<?php
	/* auth-process.php: php script for processing authentication features */
	// include libraries
	include_once('./lib/db.inc.php');
	include_once('./lib/csrf.php');
	
	// include security headers
	header('Referrer-Policy: no-referrer');
	header('Strict-Transport-Security: max-age=7776000');
	header('X-Content-Type-Options: nosniff');
	header('X-Frame-Options: DENY');
	header('X-XSS-Protection: 1; mode=block');
	
	// start PHP session
	session_set_cookie_params(0, '', '', true, true);
	session_start();
	
	function ierg4210_login(){
		// input validation and sanitization
		if(empty($_POST['email']) || empty($_POST['pw'])
			|| !preg_match("/^[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})$/", $_POST['email']))
			throw new Exception('Wrong Credentials');
		else{
			$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
			$pw = $_POST['pw'];
			
			// obtain account information given an email
			$db = ierg4210_DB();
			$sql = "SELECT * FROM account WHERE email = (?);";
			$q = $db -> prepare($sql);
			$q -> bindParam(1, $email);
			$q -> execute();
			if($res = $q -> fetch()){
				$salted_input = hash_hmac('sha256', $pw, $res['salt']);
				// validate password by verifying the hash of password 
				if($salted_input === $res['password']){
					// set an authentication token using cookies
					// name: auth; value: hashed token; expire: 3 days; property: secure, httpOnly
					$exp = time() + 3600 * 24 * 3;
					$token = array(
						'em' => $res['email'],
						'exp' => $exp,
						'k' => hash_hmac('sha256', $exp.$res['password'], $res['salt'])
					);
					setcookie('auth', json_encode($token), $exp, '', '', true, true);
					$_SESSION['auth'] = $token;
					
					// rotate session id upon successful login to mitigate session fixation vulnerabilities
					session_regenerate_id();
					
					// redirect admin to admin panel, user to main page
					if($res['admin']){
						header('Location: admin.php', true, 302);
					}else{
						header('Location: index.php', true, 302);
					}
					exit();
				}else throw new Exception('Wrong email or password');
			}else throw new Exception('Wrong email or password');
		}
	}
	
	function ierg4210_logout(){
		// clear the cookie and session
		setcookie('auth', '', time() - 1);
		$_SESSION['auth'] = null;
		
		// redirect to login page after logout
		header('Location: login.php', true, 302);
		exit();
	}
	
	function ierg4210_signup(){
		// input validation and sanitization
		if(!preg_match('/^[\w\- ]+$/', $_POST['name']))
			throw new Exception("Invalid name");
		else if(empty($_POST['email']) || empty($_POST['pw1']) || empty($_POST['pw2'])
			|| !preg_match("/^[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})$/", $_POST['email']))
			throw new Exception('Invalid email or password');
		else if($_POST['pw1'] != $_POST['pw2'])
			throw new Exception('Password not match');
		else{
			$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
			$name = filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);
			
			// obtain account information given an email
			$db = ierg4210_DB();
			$sql = "SELECT * FROM account WHERE email = (?);";
			$q = $db -> prepare($sql);
			$q -> bindParam(1, $email);
			$q -> execute();
			if($q -> fetch())
				throw new Exception('Account already exist');
			else{
				$salt = mt_rand();
				$password = hash_hmac('sha256', $_POST['pw1'], $salt);
				// insert new account
				$sql = "INSERT INTO account VALUES (null, ?, ?, ?, ?, 0);";
				$q = $db -> prepare($sql);
				$q -> bindParam(1, $email);
				$q -> bindParam(2, $name);
				$q -> bindParam(3, $salt);
				$q -> bindParam(4, $password);
				$q -> execute();
				header('Location: login.php', true, 302);
			}
		}
	}
	
	function ierg4210_changepw(){
		// input validation and sanitization
		if(empty($_POST['email']) || empty($_POST['pw'])
			|| !preg_match("/^[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})$/", $_POST['email']))
			throw new Exception('Wrong Credentials');
		else if(empty($_POST['pw1']) || empty($_POST['pw2']))
			throw new Exception('Invlid New Password');
		else if($_POST['pw1'] != $_POST['pw2'])
			throw new Exception('Password not match');
		else{
			$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
			$pw = $_POST['pw'];
			
			// obtain account information given an email
			$db = ierg4210_DB();
			$sql = "SELECT * FROM account WHERE email = (?);";
			$q = $db -> prepare($sql);
			$q -> bindParam(1, $email);
			$q -> execute();
			if ($res = $q -> fetch()){
				$salted_input = hash_hmac('sha256', $pw, $res['salt']);
				// validate password by verifying the hash of password 
				if($salted_input == $res['password']){
					$salt = mt_rand();
					$password = hash_hmac('sha256', $_POST['pw1'], $salt);
					// update the password of existing account
					$q = $db -> prepare('UPDATE account SET password = (?), salt = (?) WHERE email = (?);');
					$q -> bindParam(1, $password);
					$q -> bindParam(2, $salt);
					$q -> bindParam(3, $email);
					$q -> execute();
					// logout user after changing password
					ierg4210_logout();
					exit();
				} else throw new Exception('Wrong email or password');
			} else throw new Exception('Wrong email or password');
		}
	}
	
	header("Content-type: text/html; charset=utf-8");
	
	// input validation and sanitization
	if(empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action']))
		throw new Exception("invalid-action");
	$action = filter_var($_REQUEST['action'], FILTER_SANITIZE_SPECIAL_CHARS);
	
	try{
		// validate secret nonce from the form
		if ($action != 'logout'){
			if (empty($_POST['nonce']) || !preg_match('/^\d+$/', $_POST['nonce']))
				throw new Exception("invalid-nonce");
			$nonce = filter_var($_POST['nonce'], FILTER_SANITIZE_NUMBER_INT);
			csrf_verifyNonce($action, $nonce);
		}
		
		// call appropriate function based to the request parameter $_REQUEST['action']
		if (($returnVal = call_user_func('ierg4210_'.$action)) === false){
			header('Location: login.php', true, 302);
		}
	}catch(PDOException $e){
		echo '<h1> DB Error Occurred: </h1><p>Redirecting to login page in 3 seconds...</p>';
	}catch(Exception $e){
		echo '<h1> Error Occurred: ' . $e -> getMessage().'</h1><p>Redirecting to login page in 3 seconds...</p>';
		header('Refresh: 3; url=login.php');
	}
?>