<?php
	/* process.php: php script supporting ajax operation for product fetching */
	// include library
	include_once('./lib/db.inc.php');
	
	// include security headers
	header('Referrer-Policy: no-referrer');
	header('Strict-Transport-Security: max-age=7776000');
	header('X-Content-Type-Options: nosniff');
	header('X-Frame-Options: DENY');
	header('X-XSS-Protection: 1; mode=block');
	
	// start PHP session
	session_set_cookie_params(0, '', '', true, true);
	session_start();
	
	header('Content-Type: application/json');
	// input validation and sanitization
	if(!preg_match('/^\d*$/', $_REQUEST['id']))
		throw new Exception("invalid-pid");
	$pid = filter_var($_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT);
	try{
		// fetch a product based on its id
		if(($returnVal = call_user_func('ierg4210_prod_fetchOne', $pid)) === false){
			if($db && $db -> errorCode()) 
				error_log(print_r($db -> errorInfo(), true));
			echo json_encode(array('failed' => '1'));
		}else{
			// encode the return value in JSON format
			echo json_encode($returnVal);
		}
	}catch(PDOException $e){
		error_log($e -> getMessage());
		echo json_encode(array('failed' => 'error-db'));
	}catch(Exception $e){
		echo json_encode(array('failed' => $e -> getMessage()));
	}
?>