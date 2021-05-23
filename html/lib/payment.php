<?php
	/* payment.php: library for order and payment operations */
	/* CREATE TABLE orders (
	  oid INTEGER PRIMARY KEY,
	  user TEXT,
	  prod_list TEXT,
	  digest TEXT,
	  salt TEXT,
	  tid TEXT
	); */
	
	include_once('./db.inc.php');
	
	function ierg4210_orderDB(){
		$db = new PDO('sqlite:/var/www/orders.db');
		$db -> query('PRAGMA foreign_keys = ON;');
		$db -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $db;
	}
	
	function ierg4210_order_fetchall(){
		// obtain all order information
		$db = ierg4210_orderDB();
		$sql = "SELECT * FROM orders ORDER BY oid DESC LIMIT 50;";
		$q = $db -> prepare($sql);
		if($q -> execute())
			return $q -> fetchAll();
	}
	
	function ierg4210_order_fetch_by_oid($oid){
		// input validation and sanitization
		if(!preg_match('/^\d+$/', $oid))
			throw new Exception("invalid-oid");
		$oid = filter_var($oid, FILTER_SANITIZE_NUMBER_INT);
		
		// obtain order information given its id
		$db = ierg4210_orderDB();
		$sql = "SELECT * FROM orders WHERE oid = (?);";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $oid);
		if($q -> execute())
			return $q -> fetch();
	}
	
	function ierg4210_order_fetchall_by_email($email){
		// input validation and sanitization
		if(empty($email) || !preg_match("/^[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})$/", $email))
			throw new Exception("invalid-email");
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		
		// obtain order information given a user email
		$db = ierg4210_orderDB();
		$sql = "SELECT * FROM orders WHERE user = (?) ORDER BY oid DESC LIMIT 5;";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $email);
		if ($q -> execute())
			return $q -> fetchAll();
	}
	
	function checkTxnid($txnid){
		// check whether we've not already processed the transaction before
		// input validation and sanitization
		if(!preg_match('/^[\w\- ]+$/', $txnid))
			throw new Exception("invalid-txnid");
		$txnid = filter_var($txnid, FILTER_SANITIZE_SPECIAL_CHARS);
		
		// obtain order information given a transaction id
		$db = ierg4210_orderDB();
		$sql = 'SELECT * FROM orders WHERE tid = (?);';
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $txnid);
		if ($q -> execute())
			return !($q -> fetch());
	}

	function addPayment($data){
		// add payment record into db
		// $data is properly validated and sanitized
		if (is_array($data)) {
			// regenerate and validate the digest using the same algorithm
			$product = "";
			for ($i = 1; $i <= $data['num_cart_items']; $i++){
				$price = $data['mc_gross_'.$i] / $data['quantity_'.$i];
				$product .= ''.$data['item_name_'.$i].':'.$data['quantity_'.$i].'*'.$price.'|';
			}
			$product .= "".intval($data['payment_amount']);
			
			// obtain salt given a transaction id, update order if the integrity of request is assured
			$db = ierg4210_orderDB();
			if ($res = ierg4210_order_fetch_by_oid($data['invoice'])){
				$digest = hash_hmac('sha256', $data['payment_currency']."|".$data['receiver_email']."|".$res['salt']."|".$product, $res['salt']);
				if ($digest == $res["digest"]){
					$sql = "UPDATE orders SET prod_list=(?), tid=(?) WHERE oid=(?);";
					$q = $db -> prepare($sql);
					$q -> bindParam(1, $product);
					$q -> bindParam(2, $data['txn_id']);
					$q -> bindParam(3, $data['invoice']);
					$q->execute();
					return $db->lastInsertId();
				}
			}
		}
		return false;
	}

	function verifyTransaction($data){
		// validate the authenticity of data from PayPal
		// reference: https://www.evoluted.net/thinktank/web-development/paypal-php-integration
		
		global $paypalUrl;
		$req = 'cmd=_notify-validate';
		foreach($data as $key => $value){
			$value = urlencode(stripslashes($value));
			$value = preg_replace('/(.*[^%^0^D])(%0A)(.*)/i', '${1}%0D%0A${3}', $value); // IPN fix
			$req .= "&$key=$value";
		}

		$ch = curl_init($paypalUrl);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSLVERSION, 6);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
		$res = curl_exec($ch);

		if(!$res){
			$errno = curl_errno($ch);
			$errstr = curl_error($ch);
			curl_close($ch);
			throw new Exception("cURL error: [$errno] $errstr");
		}

		$info = curl_getinfo($ch);

		// Check the http response
		$httpCode = $info['http_code'];
		if($httpCode != 200){
			throw new Exception("PayPal responded with http code $httpCode");
		}

		curl_close($ch);
		return $res === 'VERIFIED';
	}
?>