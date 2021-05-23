<?php
	/* admin.php: web page that allow admin to manage products in database */
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
	
	// redirect to login page if the user is not authenticated
	if(!($email = auth(true))){
		header('Location: login.php');
		exit();
	}
	
	$cat_res = ierg4210_cat_fetchall();
	$prod_res = ierg4210_prod_fetchall();
	$ord_res = ierg4210_order_fetchall();
	$nav_item = '<li><a href = "index.php">Home</a></li>';
	$cat_options = '';
	$prod_options = '';
	$order_table = '';
	
	// generate all categories
	foreach($cat_res as $value){
		$nav_item .= '<li><a href="category.php?catid='.urlencode($value["catid"]).'">'.htmlspecialchars($value["name"]).'</a></li>';
		$cat_options .= '<option value="'.htmlspecialchars($value["catid"]).'"> '.htmlspecialchars($value["name"]).' </option>';
	}
	
	// generate all products
	foreach($prod_res as $value){
		$prod_options .= '<option value="'.htmlspecialchars($value["pid"]).'"> '.htmlspecialchars($value["name"]).' </option>';
	}
	
	// generate lastest 50 transaction records
	foreach($ord_res as $value){
		$order_table .= '<tr><td class="center">'.htmlspecialchars($value["oid"]).'</td><td class="center">'.htmlspecialchars($value["user"])
						.'</td><td class="center">'.htmlspecialchars($value["prod_list"]).'</td><td class="center">'.htmlspecialchars($value["digest"])
						.'</td><td class="center">'.htmlspecialchars($value["salt"]).'</td><td class="center">'.htmlspecialchars($value["tid"]).'</td></tr>';
	}
	
	// allow users to logout and change their password
	$nav_item .= '<li><a href="auth-process.php?action=logout">Logout</a></li>';
	$user = '<a href="user.php">'.htmlspecialchars(get_username_by_email($email)).'</a>';
?>

<html>
	<head>
		<title>Admin | Poke Mart</title>
		<meta charset="utf-8">
		<meta name="description" content="The admin page of PokeMart">
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
				<span>Admin</span>
			</div>
		</nav>
		
		<main>
			<h2>Welcome, <?php echo $user; ?></h2>
			<fieldset>
				<legend>New Category</legend>
				<form id="catInsertForm" method="POST" action="admin-process.php?action=cat_insert">
					<label>Name:</label>
					<div><input type="text" name="name" required="true" pattern="^[\w\- ]+$"/></div>
					<div><input type="submit" value="Submit"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("cat_insert"); ?>"/>
				</form>
			</fieldset>
			<br/>
			<fieldset>
				<legend>Edit Category</legend>
				<form id="catEditForm" method="POST" action="admin-process.php?action=cat_edit">
					<label>Category:</label>
					<div><select name="catid"><?php echo $cat_options; ?></select></div>
					<label>Name:</label>
					<div><input type="text" name="name" required="true" pattern="^[\w\- ]+$"/></div>
					<div><input type="submit" value="Submit"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("cat_edit"); ?>"/>
				</form>
			</fieldset>
			<br/>
			<fieldset>
				<legend>Delete Category</legend>
				<form id="catDeleteForm" method="POST" action="admin-process.php?action=cat_delete">
					<label>Category:</label>
					<div><select name="catid"><?php echo $cat_options; ?></select></div>
					<div><input type="submit" value="Submit"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("cat_delete"); ?>"/>
				</form>
			</fieldset>
			<br/>
			<fieldset>
				<legend>New Product</legend>
				<form id="prodInsertForm" method="POST" action="admin-process.php?action=prod_insert" enctype="multipart/form-data">
					<label>Category:</label>
					<div><select name="catid"><?php echo $cat_options; ?></select></div>
					<label>Name:</label>
					<div><input type="text" name="name" required="true" pattern="^[\w\- ]+$"/></div>
					<label>Price:</label>
					<div><input type="text" name="price" required="true" pattern="^\d+$"/></div>
					<label>Description:</label>
					<div><input type="text" name="description" required="true" pattern="^[\w\- ]+$"/></div>
					<label>Image:</label>
					<div><input type="file" name="file" required="true" accept="image/jpg, image/png, image/gif" onchange="prev_insert_image(event)"/></div>
					<img id="insert_product_image" class="thumbnail" src="" hidden=true/>
					<div><input type="submit" value="Submit"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("prod_insert"); ?>"/>
				</form>
			</fieldset>
			<br/>
			<fieldset>
				<legend>Edit Product</legend>
				<form id="prodEditForm" method="POST" action="admin-process.php?action=prod_edit" enctype="multipart/form-data">
					<label>Product:</label>
					<div><select name="pid"><?php echo $prod_options; ?></select></div>
					<label>Category:</label>
					<div><select name="catid"><?php echo $cat_options; ?></select></div>
					<label>Name:</label>
					<div><input type="text" name="name" required="true" pattern="^[\w\- ]+$"/></div>
					<label>Price:</label>
					<div><input type="text" name="price" required="true" pattern="^\d+$"/></div>
					<label>Description:</label>
					<div><input type="text" name="description" required="true" pattern="^[\w\- ]+$"/></div>
					<label>Image:</label>
					<div><input type="file" name="file" required="true" accept="image/jpeg, image/png, image/gif" onchange="prev_edit_image(event)"/></div>
					<img id="edit_product_image" class="thumbnail" src="" hidden=true/>
					<div><input type="submit" value="Submit"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("prod_edit"); ?>"/>
				</form>
			</fieldset>
			<br/>
			<fieldset>
				<legend>Delete Product</legend>
				<form id="prodDeleteForm" method="POST" action="admin-process.php?action=prod_delete">
					<label>Product:</label>
					<div><select name="pid"><?php echo $prod_options; ?></select></div>
					<div><input type="submit" value="Submit"/></div>
					<input type="hidden" name="nonce" value="<?php echo csrf_getNonce("prod_delete"); ?>"/>
				</form>
			</fieldset>
			<br/>
			<fieldset>
				<legend>Lastest 50 Transaction Records</legend>
				<table id="orderTable">
					<tr>
						<th class="center">Order ID</td>
						<th class="center">User Account</td>
						<th class="center">Product List (name:quantity*price|...|total)</td>
						<th class="center">Digest</td>
						<th class="center">Salt</td>
						<th class="center">Payment Status</td>
					</tr>
					<?php echo $order_table; ?>
				</table>
				<p>Remark: The payment status field will show the corresponding transaction ID if the payment succeed.</p>
			</fieldset>
		</main>
		
		<footer>
			<p>Copyright &copy; 2021 Poke Mart. All right reserved.</p>
		</footer>
		
		<script type="text/javascript" src="./lib/myLib-min.js"></script>
		<script type="text/javascript" src="./js/manageCart-min.js"></script>
		<script type="text/javascript">
			// graceful degradation: use javascript validation in case HTML5 is not supported
			var catInsertForm = document.querySelector('#catInsertForm');
			var catEditForm = document.querySelector('#catEditForm');
			var catDeleteForm = document.querySelector('#catDeleteForm');
			var prodInsertForm = document.querySelector('#prodInsertForm');
			var prodEditForm = document.querySelector('#prodEditForm');
			var prodDeleteForm = document.querySelector('#prodDeleteForm');
			
			catInsertForm.onsubmit = function(){
				if(!catInsertForm.checkValidity || catInsertForm.noValidate)
					return myLib.validate(this);
			}
			
			catEditForm.onsubmit = function(){
				if(!catEditForm.checkValidity || catEditForm.noValidate)
					return myLib.validate(this);
			}
			
			catDeleteForm.onsubmit = function(){
				if(!catDeleteForm.checkValidity || catDeleteForm.noValidate)
					return myLib.validate(this);
			}
			
			prodInsertForm.onsubmit = function(){
				if(!prodInsertForm.checkValidity || prodInsertForm.noValidate)
					return myLib.validate(this);
			}
			
			prodEditForm.onsubmit = function(){
				if(!prodEditForm.checkValidity || prodEditForm.noValidate)
					return myLib.validate(this);
			}
			
			prodDeleteForm.onsubmit = function(){
				if(!prodDeleteForm.checkValidity || prodDeleteForm.noValidate)
					return myLib.validate(this);
			}
			
			// preview product image before a new product is added
			function prev_insert_image(event){
				var reader = new FileReader();
				reader.onload = function(){
					document.querySelector("#insert_product_image").hidden = false;
					document.querySelector("#insert_product_image").src = reader.result.escapeHTML();
				}
				if(event.target.files[0] != null){
					reader.readAsDataURL(event.target.files[0]);
				}else{
					document.querySelector("#insert_product_image").hidden = true;
					document.querySelector("#insert_product_image").src = "";
				}
			}
			
			// preview product image before a product is edited
			function prev_edit_image(event){
				var reader = new FileReader();
				reader.onload = function(){
					document.querySelector("#edit_product_image").hidden = false;
					document.querySelector("#edit_product_image").src = reader.result.escapeHTML();
				}
				if(event.target.files[0] != null){
					reader.readAsDataURL(event.target.files[0]);
				}else{
					document.querySelector("#edit_product_image").hidden = true;
					document.querySelector("#edit_product_image").src = "";
				}
			}
		</script>
	</body>
</html>
