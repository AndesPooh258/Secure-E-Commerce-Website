<?php
	/* user.php: web page displaying change password form and most recent 5 orders of a user */
	// include libraries
	include_once('./lib/db.inc.php');
	include_once('./lib/auth.php');
	include_once('./lib/csrf.php');
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
	
	$cat_res = ierg4210_cat_fetchall();
	$nav_item = '';
	$prod_list = '';
	
	// provide access to admin page if an admin is authenticated
	if(auth(true)){
		$nav_item .= '<li><a href="admin.php">Admin</a></li>';
	}
	
	// generate all categories
	foreach($cat_res as $value){
		$nav_item .= '<li><a href="category.php?catid='.urlencode($value["catid"]).'">'.htmlspecialchars($value["name"]).'</a></li>';
	}
	
	if($email = auth()){
		// allow users to logout
		$nav_item .= '<li><a href="auth-process.php?action=logout">Logout</a></li>';
	}else{
		// redirect to login page if the user is not authenticated
		header('Location: login.php');
		exit();
	}
	
	$ord_res = ierg4210_order_fetchall_by_email($email);
	
	// generate lastest 5 transaction records
	foreach($ord_res as $value){
		if($value["tid"] !== "Incomplete")
			$status = "Success";
		else $status = "Incomplete";
		$order_table .= '<tr><td class="center">'.htmlspecialchars($value["oid"]).'</td><td class="center">'.htmlspecialchars($value["prod_list"]).
						'</td><td class="center">'.$status.'</td></tr>';
	}
	
	// check user name by email
	$user_name = get_username_by_email($email);
?>
<!DOCTYPE html>
<html>
	<head>
		<title>User | Poke Mart</title>
		<meta charset="utf-8">
		<meta name="description" content="The user page of PokeMart">
		<meta name="author" content="Andes KEI">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="favicon.ico" rel="icon" type="image/x-icon">
		<link rel="stylesheet" href="./css/style-min.css"/>
	</head>
	<body onload="refreshCart()">
		<?php readfile('./static/header.html'); ?>
		
		<nav>
			<div class="nav-bar">
				<ul class="category-list">
					<?php echo $nav_item; ?>
				</ul>
			</div>
			<div class="nav-menu">
				<a href="index.php"><span>Home</span></a>
				&gt;
				<span>User</span>
			</div>
		</nav>
		
		<main>
			<h2>User Name: <?php echo htmlspecialchars($user_name); ?></h2>
			<fieldset>
				<legend>Change Password</legend>
				<form id="changepwForm" method="POST" action="auth-process.php?action=changepw">
					<input type="hidden" name="email" required="true" value="<?php echo htmlspecialchars($email); ?>"/>
					<label>Old Password:</label>
					<div><input type="password" name="pw" required="true"/></div>
					<label>New Password:</label>
					<div><input type="password" name="pw1" required="true"/></div>
					<label>Repeat Password:</label>
					<div><input type="password" name="pw2" required="true"/></div>
					<div><input type="submit" value="Change Password"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("changepw"); ?>"/>
				</form>
			</fieldset>
			<br/>
			<fieldset>
				<legend>Lastest 5 Transaction Records</legend>
				<table id="orderTable">
					<tr>
						<th class="center">Order ID</td>
						<th class="center">Product List (name:quantity*price|...|total)</td>
						<th class="center">Payment Status</td>
					</tr>
					<?php echo $order_table; ?>
				</table>
			</fieldset>
		</main>
		
		<footer>
			<p>Copyright &copy; 2021 Poke Mart. All right reserved.</p>
		</footer>
		
		<script type="text/javascript" src="./lib/myLib-min.js"></script>
		<script type="text/javascript" src="./js/manageCart-min.js"></script>
		<script type="text/javascript">
			// graceful degradation: use javascript validation in case HTML5 is not supported
			var changepwForm = document.querySelector('#changepwForm');
			changepwForm.onsubmit = function(){
				if(changepwForm.email.value == ''){
					alert('FieldError: email is required');
					return false;
				}else if(!new RegExp(/[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})/).test(changepwForm.email.value)){
					alert('FieldError: invalid email');
					return false;
				} 
				if (!changepwForm.checkValidity || changepwForm.noValidate)
					return myLib.validate(this);
			}
		</script>
	</body>
</html>