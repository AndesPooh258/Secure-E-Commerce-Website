<?php
	/* detail.php: login page of the website */
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
	
	// redirect users if they are authentucated
	if(auth(true)){
		header('Location: admin.php');
		exit();
	}else if(auth()){
		header('Location: index.php');
		exit();
	}
	
	$cat_res = ierg4210_cat_fetchall();
	$prod_res = ierg4210_prod_fetchall();
	$nav_item = '<li><a href = "index.php">Home</a></li>';
	
	// generate all categories
	foreach($cat_res as $value){
		$nav_item .= '<li><a href="category.php?catid='.urlencode($value["catid"]).'">'.htmlspecialchars($value["name"]).'</a></li>';
	}
?>

<html>
	<head>
		<title>Login | Poke Mart</title>
		<meta charset="utf-8">
		<meta name="description" content="The login page of PokeMart">
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
				<span>Login</span>
			</div>
		</nav>
		
		<main>
			<h1>Welcome to Poke Mart!</h1>
			<fieldset>
				<legend>Login</legend>
				<form id="loginForm" method="POST" action="auth-process.php?action=login">
					<label>Email:</label>
					<div><input type="email" name="email" required="true" pattern="^[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})$"/></div>
					<label>Password:</label>
					<div><input type="password" name="pw" required="true"/></div>
					<div><input type="submit" value="Login"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("login"); ?>"/>
				</form>
			</fieldset>
			<br/>
			<fieldset>
				<legend>Sign Up</legend>
				<form id="signupForm" method="POST" action="auth-process.php?action=signup">
					<label>Email:</label>
					<div><input type="email" name="email" required="true" pattern="^[\w\/-][\w'\/\.-]*@[\w-]+(\.[\w-]+)*(\.[\w]{2,6})$"/></div>
					<label>Name:</label>
					<div><input type="text" name="name" required="true" pattern="^[\w\- ]+$"/></div>
					<label>Password:</label>
					<div><input type="password" name="pw1" required="true"/></div>
					<label>Repeat Password:</label>
					<div><input type="password" name="pw2" required="true"/></div>
					<div><input type="submit" value="Sign Up"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("signup"); ?>"/>
				</form>
			</fieldset>
			<br/>
		</main>
		
		<footer>
			<p>Copyright &copy; 2021 Poke Mart. All right reserved.</p>
		</footer>
		
		<script type="text/javascript" src="./lib/myLib-min.js"></script>
		<script type="text/javascript" src="./js/manageCart-min.js"></script>
		<script type="text/javascript">
			// graceful degradation: use javascript validation in case HTML5 is not supported
			var loginForm = document.querySelector('#loginForm');
			var signupForm = document.querySelector('#signupForm');
			
			loginForm.onsubmit = function(){
				if (!loginForm.checkValidity || loginForm.noValidate)
					return myLib.validate(this);
			}
			
			signupForm.onsubmit = function(){
				if (!signupForm.checkValidity || signupForm.noValidate)
					return myLib.validate(this);
			}
		</script>
	</body>
</html>
