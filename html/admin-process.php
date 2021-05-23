<?php
	/* admin-process.php: php script for processing admin features */
	// include libraries
	include_once('./lib/db.inc.php');
	include_once('./lib/auth.php');
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
	
	// redirect to login page if the user is not authenticated
	if (!auth(true)){
		header('Location: login.php');
		exit();
	}
	
	header('Content-Type: application/json');

	// input validation and sanitization
	if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])){
		echo json_encode(array('failed' => 'undefined'));
		exit();
	}
	if (empty($_POST['nonce']) || !preg_match('/^\d+$/', $_POST['nonce'])){
		echo json_encode(array('failed' => 'undefined'));
		exit();
	}
	$action = filter_var($_REQUEST['action'], FILTER_SANITIZE_SPECIAL_CHARS);
	$nonce = filter_var($_POST['nonce'], FILTER_SANITIZE_NUMBER_INT);
	
	try{
		// validate secret nonce from the form
		csrf_verifyNonce($action, $nonce);
		
		// call appropriate function based to the request parameter $_REQUEST['action']
		if (($returnVal = call_user_func('ierg4210_'.$action)) === false){
			if ($db && $db -> errorCode()) 
				error_log(print_r($db -> errorInfo(), true));
			echo json_encode(array('failed' => '1'));
		}
		
		// encode the return value in JSON format
		echo json_encode(array('success' => $returnVal));
	}catch(PDOException $e){
		error_log($e -> getMessage());
		echo json_encode(array('failed' => 'error-db'));
	}catch(Exception $e){
		echo json_encode(array('failed' => $e -> getMessage()));
	}
?>