<?php
	/* db.inc.php: library for product and user related operations */
	/* CREATE TABLE categories (
		catid INTEGER PRIMARY KEY,
		name TEXT
	);
	CREATE TABLE products (
		pid INTEGER PRIMARY KEY,
		catid INTEGER,
		name TEXT,
		price INTEGER,
		description TEXT,
		FOREIGN KEY(catid) REFERENCES categories(catid)
	); */

	function ierg4210_DB(){
		// connect to the database, change the following path if needed
		// Warning: NEVER put your db in a publicly accessible location
		$db = new PDO('sqlite:/var/www/cart.db');

		// enable foreign key support
		$db -> query('PRAGMA foreign_keys = ON;');

		// FETCH_ASSOC:
		// Specifies that the fetch method shall return each row as an
		// array indexed by column name as returned in the corresponding
		// result set. If the result set contains multiple columns with
		// the same name, PDO::FETCH_ASSOC returns only a single value
		// per column name.
		$db -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

		return $db;
	}
	
	function ierg4210_cat_fetchall(){
		// obtain all category information
		$db = ierg4210_DB();
		$sql = "SELECT * FROM categories LIMIT 100;";
		$q = $db -> prepare($sql);
		if($q -> execute())
			return $q -> fetchAll();
	}
	
	function ierg4210_get_name_by_catid($catid){
		// input validation and sanitization
		if(!preg_match('/^\d*$/', $catid))
			throw new Exception("invalid-catid");
		$catid = filter_var($catid, FILTER_SANITIZE_NUMBER_INT);
		
		// obtain category name given its id
		$db = ierg4210_DB();
		$sql = "SELECT name FROM categories WHERE catid=(?);";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $catid);
		if($q -> execute())
			return $q -> fetch()['name']; 
	}
	
	function ierg4210_cat_insert(){
		// input validation and sanitization
		if(!preg_match('/^[\w\- ]+$/', $_POST['name']))
			throw new Exception("invalid-name");
		$name = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);
		
		// insert new category
		$db = ierg4210_DB();
		$sql = "INSERT INTO categories VALUES (null, ?);";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $name);
		$q -> execute();
		
		header('Location: admin.php');
		exit();
	}
	
	function ierg4210_cat_edit(){
		// input validation and sanitization
		if(!preg_match('/^[\w\- ]+$/', $_POST['name']))
			throw new Exception("invalid-name");
		if(!preg_match('/^\d*$/', $_POST['catid']))
			throw new Exception("invalid-catid");
		$name = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);
		$catid = filter_var($_POST["catid"], FILTER_SANITIZE_NUMBER_INT);
		
		// edit existing category
		$db = ierg4210_DB();
		$sql = "UPDATE categories SET name=(?) WHERE catid=(?);";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $name);
		$q -> bindParam(2, $catid);
		$q -> execute();
		
		header('Location: admin.php');
		exit();
	}
	
	function ierg4210_cat_delete(){
		// input validation and sanitization
		if(!preg_match('/^\d*$/', $_POST['catid']))
			throw new Exception("invalid-catid");
		$catid = filter_var($_POST["catid"], FILTER_SANITIZE_NUMBER_INT);
		
		// delete existing category
		$db = ierg4210_DB();
		ierg4210_prod_delete_by_catid($catid);
		$sql = "DELETE FROM categories WHERE catid=(?);";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $catid);
		$q -> execute();
		
		header('Location: admin.php');
		exit();
	}
	
	function ierg4210_prod_fetchAll(){
		// obtain all product information
		$db = ierg4210_DB();
		$sql = "SELECT * FROM products LIMIT 100;";
		$q = $db -> prepare($sql);
		if($q -> execute())
			return $q -> fetchAll();
	}
	
	function ierg4210_prod_fetch_by_catid($catid){
		// input validation and sanitization
		if(!preg_match('/^\d+$/', $catid))
			throw new Exception("invalid-catid");
		$catid = filter_var($catid, FILTER_SANITIZE_NUMBER_INT);
		
		// obtain product information given a category id
		$db = ierg4210_DB();
		$sql = "SELECT * FROM products WHERE catid=(?);";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $catid);
		if($q -> execute())
			return $q -> fetchAll();
	}
	
	function ierg4210_prod_fetchOne($pid){
		// input validation and sanitization
		if(!preg_match('/^\d+$/', $pid))
			throw new Exception("invalid-pid");
		$pid = filter_var($pid, FILTER_SANITIZE_NUMBER_INT);
		
		// obtain a product given its id
		$db = ierg4210_DB();
		$sql = "SELECT * FROM products WHERE pid=(?);";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $pid);
		if ($q -> execute())
			return $q -> fetch();
	}
	
	function ierg4210_prod_insert() {
		// input validation and sanitization
		if($_FILES["file"] == null)
			throw new Exception("cannot upload file");
		if(!preg_match('/^\d+$/', $_POST['catid']))
			throw new Exception("invalid-catid");
		$_POST['catid'] = (int) $_POST['catid'];
		if(!preg_match('/^[\w\- ]+$/', $_POST['name']))
			throw new Exception("invalid-name");
		if(!preg_match('/^\d+$/', $_POST['price']))
			throw new Exception("invalid-price");
		if(!preg_match('/^[\w\- ]+$/', $_POST['description']))
			throw new Exception("invalid-text");
		$catid = filter_var($_POST["catid"], FILTER_SANITIZE_NUMBER_INT);
		$name = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);
		$price = filter_var($_POST["price"], FILTER_SANITIZE_NUMBER_INT);
		$desc = filter_var($_POST["description"], FILTER_SANITIZE_SPECIAL_CHARS);

		// to prevent file-based XSS injection, we need to check the MIME-type at server side
		if($_FILES["file"]["error"] == 0
			&&($_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/gif")
			&&(mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg" || mime_content_type($_FILES["file"]["tmp_name"]) == "image/png"
			|| mime_content_type($_FILES["file"]["tmp_name"]) == "image/gif") && $_FILES["file"]["size"] <= 10485760){
			
			// insert a new product
			$db = ierg4210_DB();
			$sql = "INSERT INTO products (catid, name, price, description) VALUES (?, ?, ?, ?);";
			$q = $db -> prepare($sql);
			$q -> bindParam(1, $catid);
			$q -> bindParam(2, $name);
			$q -> bindParam(3, $price);
			$q -> bindParam(4, $desc);
			$q -> execute();
			$lastId = $db -> lastInsertId();

			if(move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/img/".$lastId.".png")) {
				// redirect back to original page
				header('Location: admin.php');
				exit();
			}
		}
		
		// only an invalid file will result in the execution below
		// replace the content-type header which was json and output an error message
		header('Content-Type: text/html; charset=utf-8');
		echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
		exit();
	}
	
	function ierg4210_prod_edit(){
		// input validation and sanitization
		if($_FILES["file"] == null)
			throw new Exception("cannot upload file");
		if(!preg_match('/^\d+$/', $_POST['pid']))
			throw new Exception("invalid-pid");
		if(!preg_match('/^\d+$/', $_POST['catid']))
			throw new Exception("invalid-catid");
		$_POST['catid'] = (int) $_POST['catid'];
		if(!preg_match('/^[\w\- ]+$/', $_POST['name']))
			throw new Exception("invalid-name");
		if(!preg_match('/^\d+$/', $_POST['price']))
			throw new Exception("invalid-price");
		if(!preg_match('/^[\w\- ]+$/', $_POST['description']))
			throw new Exception("invalid-text");
		$catid = filter_var($_POST["catid"], FILTER_SANITIZE_NUMBER_INT);
		$name = filter_var($_POST["name"], FILTER_SANITIZE_SPECIAL_CHARS);
		$price = filter_var($_POST["price"], FILTER_SANITIZE_NUMBER_INT);
		$desc = filter_var($_POST["description"], FILTER_SANITIZE_SPECIAL_CHARS);
		$pid = filter_var($_POST["pid"], FILTER_SANITIZE_NUMBER_INT);
		
		if($_FILES["file"]["error"] == 0
			&&($_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/gif")
			&&(mime_content_type($_FILES["file"]["tmp_name"]) == "image/jpeg" || mime_content_type($_FILES["file"]["tmp_name"]) == "image/png"
			|| mime_content_type($_FILES["file"]["tmp_name"]) == "image/gif") && $_FILES["file"]["size"] <= 10485760){
			// edit existing product
			$db = ierg4210_DB();
			$sql = "UPDATE products SET catid=(?), name=(?), price=(?), description=(?) WHERE pid=(?);";
			$q = $db -> prepare($sql);
			$q -> bindParam(1, $catid);
			$q -> bindParam(2, $name);
			$q -> bindParam(3, $price);
			$q -> bindParam(4, $desc);
			$q -> bindParam(5, $pid);
			$q -> execute();

			if(move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/img/".$pid.".png")){
				header('Location: admin.php');
				exit();
			}
		}
		
		header('Content-Type: text/html; charset=utf-8');
		echo 'Invalid file detected. <br/><a href="javascript:history.back();">Back to admin panel.</a>';
		exit();
	}
	
	function ierg4210_prod_delete(){
		// input validation and sanitization
		if(!preg_match('/^\d+$/', $_POST['pid']))
			throw new Exception("invalid-pid");
		$pid = filter_var($_POST["pid"], FILTER_SANITIZE_NUMBER_INT);
		
		// delete existing product
		$db = ierg4210_DB();
		$sql = "DELETE FROM products WHERE pid=(?);";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $pid);
		if($q -> execute()){
			// remove the product image after deletion
			unlink("/var/www/html/img/".$pid.".png");
		}
		
		header('Location: admin.php');
		exit();
	}
	
	function ierg4210_prod_delete_by_catid($catid){
		// input validation and sanitization
		if (!preg_match('/^\d+$/', $catid))
			throw new Exception("invalid-catid");
		$catid = filter_var($_POST["catid"], FILTER_SANITIZE_NUMBER_INT);
		
		// delete all products of a given category
		$db = ierg4210_DB();
		$sql = "SELECT pid FROM products WHERE catid=(?);";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $catid);
		if($q -> execute()){
			$result = $q -> fetchAll();
			foreach($result as $value)
				unlink("/var/www/html/img/".$value["pid"].".png");
		}
		// delete existing category
		$sql = "DELETE FROM products WHERE catid=(?)";
		$q = $db -> prepare($sql);
		$q -> bindParam(1, $catid);
		$q -> execute();
	}
?>