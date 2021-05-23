<?php
	/* index.php: web page displaying all available products of the shop */
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
	$prod_res = ierg4210_prod_fetchall();
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
	
	// generate all products
	foreach($prod_res as $value){
		$prod_list .= '<li class="list-inline-item center"><a href="detail.php?pid='.urlencode($value["pid"]).
						'"><img class="thumbnail" src="img/'.urlencode($value["pid"]).'.png" alt="'.htmlspecialchars($value["name"]).
						'"/><br/>'.htmlspecialchars($value["name"]).'</a><br/>$'.htmlspecialchars($value["price"]).
						' <button type="button" onclick="addtocart('.htmlspecialchars($value["pid"]).')">Add</button></li>';
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Poke Mart</title>
		<meta charset="utf-8">
		<meta name="description" content="The main page displaying all available products in PokeMart">
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
				<span>Home</span>
			</div>
		</nav>
		
		<main>
			<h2>Welcome, <?php echo $user; ?></h2>
			<section class="product-list">
				<ul class="list-inline">
					<?php echo $prod_list; ?>
				</ul>
			</section>
		</main>
		
		<footer>
			<p>Copyright &copy; 2021 Poke Mart. All right reserved.</p>
		</footer>
		
		<script type="text/javascript" src="./lib/myLib-min.js"></script>
		<script type="text/javascript" src="./js/manageCart-min.js"></script>
	</body>
</html>