<?php
	/* payment-success.php: web page for displaying the payment success message */
	// include libraries
	include_once('./lib/db.inc.php');
	include_once('./lib/auth.php');
	
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
	
	// provide access to admin page if an admin is authenticated
	if(auth(true)){
		$nav_item .= '<li><a href="admin.php">Admin</a></li>';
	}
	
	$nav_item .= '<li><a href = "index.php">Home</a></li>';
	
	// generate all categories
	foreach($cat_res as $value){
		$nav_item .= '<li><a href="category.php?catid='.urlencode($value["catid"]).'">'.htmlspecialchars($value["name"]).'</a></li>';
	}
	
	// check user name by email
	$email = auth();
	$user_name = get_username_by_email($email);
	
	if($email){
		// allow users to logout and change their password
		$nav_item .= '<li><a href="auth-process.php?action=logout">Logout</a></li>';
		$user = '<a href="user.php">'.htmlspecialchars($user_name).'</a>';
	}else{
		// allow guests to login
		$nav_item .= '<li><a href="login.php">Login</a></li>';
		$user = htmlspecialchars($user_name);
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Payment Success | Poke Mart</title>
		<meta charset="utf-8">
		<meta name="description" content="The payment success page of PokeMart">
		<meta name="author" content="Andes KEI">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="favicon.ico" rel="icon" type="image/x-icon">
		<link rel="stylesheet" href="./css/style-min.css"/>
		<style>
			main {
				text-align: center;
			}
			.card {
				margin: 0 auto;
				background: #EBF0F5;
				padding: 40px;
				border-radius: 4px;
				box-shadow: 0 2px 3px #C8D0D8;
				display: inline-block;
			}
			.success-heading {
			  color: #88B04B;
			  font-family: sans-serif;
			  font-weight: 900;
			  font-size: 40px;
			  margin-bottom: 10px;
			}
			.success-message {
			  color: #404F5E;
			  font-family: sans-serif;
			  font-size:20px;
			  margin: 0;
			}
			.circle-area {
				border-radius:200px; 
				height:200px; 
				width:200px; 
				background: #F8FAF5; 
				margin:0 auto;
			}
			i {
				color: #9ABC66;
				font-size: 100px;
				line-height: 200px;
				margin-left:-15px;
			}
		</style>
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
				<span>Payment</span>
			</div>
		</nav>
		
		<main>
			<div class="card">
				<div class="circle-area">
					<i>✓</i>
				</div>
				<h1 class="success-heading">Success</h1> 
				<p class="success-message">We received your purchase request.<br/> We'll be in touch shortly!</p>
			</div>
		</main>
		
		<footer>
			<p>Copyright &copy; 2021 Poke Mart. All right reserved.</p>
		</footer>
		
		<script type="text/javascript" src="./lib/myLib-min.js"></script>
		<script type="text/javascript" src="./js/manageCart-min.js"></script>
	</body>
</html>