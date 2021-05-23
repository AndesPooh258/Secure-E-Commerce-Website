<?php
	/* detail.php: web page displaying details of a product */
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
	$nav_menu = '';
	$nav_item = '';
	$prod_info = '';
	
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
	
	// database query for product
	try{
		$prod_res = ierg4210_prod_fetchOne($_GET['pid']);
		$cat_name = ierg4210_get_name_by_catid($prod_res['catid']);
	}catch(exception $e){
		$prod_res = null;
		$cat_name = null;
	}
	
	if($cat_name == null || $prod_res == null){
		$title = '<title>Product Not Found | Poke Mart</title>';
	}else{
		// generate title
		$title = '<title>'.htmlspecialchars($prod_res["name"]).' | Poke Mart</title>';
		// generate navigation menu
		$nav_menu = '<div class="nav-menu">
						<a href="index.php"><span>Home</span></a>
						&gt;
						<span><a href="category.php?catid='.urlencode($prod_res['catid']).'"><span>'.htmlspecialchars($cat_name).'</span></a></span>
						&gt;
						<span>'.htmlspecialchars($prod_res["name"]).'</span>
					 </div>';
		// generate product information
		$prod_info = '<section class="product-info">
						<h2>'.htmlspecialchars($prod_res["name"]).'</h2>
						<div class="grid-box">
							<div>
								<img class="larger" src="./img/'.urlencode($prod_res["pid"]).'.png" alt="'.htmlspecialchars($prod_res["name"]).'"/>
							</div>
							<div>
								<h2 class="product-price">$'.htmlspecialchars($prod_res["price"]).
								' <button type="button" onclick="addtocart('.htmlspecialchars($prod_res["pid"]).')">Add</button></h2><br/>
								<p>Description: '.htmlspecialchars($prod_res["description"]).'</p><br/>
							</div>
						</div>
					  </section>';
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php echo $title; ?>
		<meta charset="utf-8">
		<meta name="description" content="The product page displaying details of product <?php echo htmlspecialchars($prod_res["name"]); ?>">
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
			<?php echo $nav_menu; ?>
		</nav>
		
		<main>
			<h2>Welcome, <?php echo $user; ?></h2>
			<?php echo $prod_info; ?>
		</main>
		
		<footer>
			<p>Copyright &copy; 2021 Poke Mart. All right reserved.</p>
		</footer>
		
		<script type="text/javascript" src="./lib/myLib-min.js"></script>
		<script type="text/javascript" src="./js/manageCart-min.js"></script>
	</body>
</html>