<?php
	/* csrf.php: library for mitigating CSRF vulnerabilities */
	function csrf_getNonce($action){
		// generate a nonce with mt_rand()
		$nonce = mt_rand().mt_rand();
		
		// with regard to $action, save the nonce in $_SESSION 
		if(!isset($_SESSION['csrf_nonce']))
			$_SESSION['csrf_nonce'] = array();
		$_SESSION['csrf_nonce'][$action] = $nonce;
		
		// return the nonce
		return $nonce;
	}

	function csrf_verifyNonce($action, $receivedNonce){
		// check if the nonce returned by a form matches with the stored one.
		// $REQUEST['action'] is properly validated and sanitized
		if(isset($receivedNonce) && $_SESSION['csrf_nonce'][$action] == $receivedNonce){
			if($_SESSION['auth'] == null)
				unset($_SESSION['csrf_nonce'][$action]);
			return true;
		}
		throw new Exception('csrf-attack');
	}
?>