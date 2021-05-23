<?php
	/* checkout-process.php: php script for processing checkout features */
	// include libraries
	include_once('./lib/db.inc.php');
	include_once('./lib/auth.php');
	include_once('./lib/payment.php');
	
	// include security headers
	header('Referrer-Policy: no-referrer');
	header('Strict-Transport-Security: max-age=7776000');
	header('X-Content-Type-Options: nosniff');
	header('X-Frame-Options: DENY');
	header('X-XSS-Protection: 1; mode=block');
	
	// start PHP session
	session_set_cookie_params(0, '', '', true, true);
	session_start();
	
	function ierg4210_checkout(){
		$cart = json_decode($_POST["cart"]);
		$product = "";
		$total = 0;
		$items = [];
		
		// prepare data for generating the digest
		foreach($cart as $pid => $quantity){
			// input validation and sanitization
			if(!preg_match('/^\d+$/', $pid))
				continue;
			if(!preg_match('/^\d+$/', $quantity))
				continue;
			$pid = filter_var($pid, FILTER_SANITIZE_NUMBER_INT);
			$quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);
			// obtain product information given its id
			if($res = ierg4210_prod_fetchOne($pid)){
				$name = filter_var($res["name"], FILTER_SANITIZE_SPECIAL_CHARS);
				$price = filter_var($res["price"], FILTER_SANITIZE_NUMBER_INT);
				$product .= ''.$name.':'.$quantity.'*'.$price.'|';
				$total += $price * $quantity;
			}
			array_push($items, [$name, $price, $quantity]);
		}
		$product .= "".$total;
		
		$currency = "HKD";
		$email = "admin@pokemart.com";
		$salt = mt_rand() . mt_rand();
		
		$digest = hash_hmac('sha256', $currency."|".$email."|".$salt."|".$product, $salt);
		
		if(!($user = auth()))
			$user = 'Guest';
		
		// insert new order
		$db = ierg4210_orderDB();
		$sql = 'INSERT INTO orders VALUES (null, ?, ?, ?, ?, "Incomplete")';
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $user);
		$q -> bindParam(2, $product);
		$q -> bindParam(3, $digest);
		$q -> bindParam(4, $salt);
		$q -> execute();
		
		$invoice = $db -> lastInsertId();
		return array("items" => $items, "custom" => $digest, "invoice" => $invoice);
	}
	
	header('Content-Type: application/json');

	try{
		if(($returnVal = call_user_func('ierg4210_checkout')) === false){
			if($db && $db -> errorCode())
				error_log(print_r($db -> errorInfo(), true));
			echo json_encode(array('failed' => '1'));
		}
		echo json_encode($returnVal);
	}catch(PDOException $e){
		error_log($e -> getMessage());
		echo json_encode(array('failed' => 'error-db'));
	}catch(Exception $e){
		echo json_encode(array('failed' => $e -> getMessage()));
	}
?>