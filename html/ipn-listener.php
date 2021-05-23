<?php
	/* ipn-listener.php: listener for catching the IPN message sent from PayPal */
	// include library
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
	
	// for test payments we want to enable the sandbox mode. If you want to put live
	// payments through then this setting needs changing to 'false'
	$enableSandbox = true;
	$paypalUrl = $enableSandbox ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
	
	// input validation and sanitization
	if(!preg_match('/^\w+$/', $_POST['txn_id']))
		throw new Exception("invalid-txn_id");
	if(!preg_match('/^\w+$/', $_POST['txn_type']))
		throw new Exception("invalid-txn_type");
	if(!preg_match('/^\w+$/', $_POST['num_cart_items']))
		throw new Exception("invalid-num_cart_items");
	if(!preg_match('/^\d*.?\d+$/', $_POST['mc_gross']))
		throw new Exception("invalid-mc_gross");
	if(!preg_match('/^\w+$/', $_POST['mc_currency']))
		throw new Exception("invalid-mc_currency");
	if(!preg_match("/^[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})$/", $_POST['receiver_email']))
		throw new Exception("invalid-receiver_email");
	if(!preg_match("/^\w{64}$/", $_POST['custom']))
		throw new Exception("invalid-custom");
	if(!preg_match("/^\d+$/", $_POST['invoice']))
		throw new Exception("invalid-invoice");
	
	// assign posted variables to local data array
	$data = [
		'txn_id' => filter_var($_POST['txn_id'], FILTER_SANITIZE_SPECIAL_CHARS),
		'num_cart_items' => filter_var($_POST['num_cart_items'], FILTER_SANITIZE_NUMBER_INT),
		'payment_amount' => filter_var($_POST['mc_gross'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
		'payment_currency' => filter_var($_POST['mc_currency'], FILTER_SANITIZE_SPECIAL_CHARS),
		'receiver_email' => filter_var($_POST['receiver_email'], FILTER_SANITIZE_EMAIL),
		'custom' => filter_var($_POST['custom'], FILTER_SANITIZE_SPECIAL_CHARS),
		'invoice' => filter_var($_POST['invoice'], FILTER_SANITIZE_NUMBER_INT),
	];
		
	for($i = 1; $i <= $data['num_cart_items']; $i++){
		if(!preg_match('/^[\w\- ]+$/', $_POST['item_name'.$i]))
			throw new Exception("invalid-item_name".$i);
		if(!preg_match('/^\d*.?\d+$/', $_POST['mc_gross_'.$i]))
			throw new Exception("invalid-mc_gross_".$i);
		if(!preg_match('/^\d+$/', $_POST['quantity'.$i]))
			throw new Exception("invalid-quantity".$i);
		$data['item_name_'.$i] = filter_var($_POST['item_name'.$i], FILTER_SANITIZE_SPECIAL_CHARS);
		$data['mc_gross_'.$i] = filter_var($_POST['mc_gross_'.$i], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		$data['quantity_'.$i] = filter_var($_POST['quantity'.$i], FILTER_SANITIZE_NUMBER_INT);
	}

	// verify transaction and check transaction id
	if(verifyTransaction($_POST) && checkTxnid($data['txn_id'])){
		if(addPayment($data) !== false){
			// payment successfully added
			echo json_encode(array('success' => '1'));
		}
	}else{
		// payment failed
		echo json_encode(array('failed' => '1'));
	}
	
?>