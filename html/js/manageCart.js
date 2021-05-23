/* manageCart.js: javascript for shopping cart related functions */
function refreshCart(){
	totalPrice = 0;
	
	// read from local storage
	storage = window.localStorage.getItem('cart_storage');
	storage = storage ? JSON.parse(storage):{};
	
	document.querySelector(".shopping-cart").innerHTML = "";
	for(var pid in storage){
		myLib.ajax({
			url:'process.php?id=' + encodeURIComponent(pid.toString()),
			success:function(m){
				product = JSON.parse(m);
				if(product.failed == undefined){
					// generate visible shopping list
					var arr = [];
					arr.push('<li><input id="q_'); arr.push(product.pid.toString().escapeHTML());
					arr.push('" class="quantity" type="number" name="num" value="'); arr.push(storage[product.pid].toString().escapeHTML());
					arr.push('" onchange="changeQuantity('); arr.push(product.pid.toString().escapeHTML()); arr.push(')"/> ');
					arr.push(product.name.toString().escapeHTML()); arr.push(' @ $'); arr.push(product.price.toString().escapeHTML()); 
					arr.push('</li>');
					document.querySelector(".shopping-cart").innerHTML += arr.join('');
					
					// calculate total price for users
					totalPrice += product.price * storage[product.pid];
					document.querySelector("#price").innerHTML = '<p>Total Price = $' + totalPrice.toString().escapeHTML() + '</p>';
				}
			}
		})
	}
	document.querySelector("#price").innerHTML = '<p>Total Price = $' + totalPrice.toString().escapeHTML() + '</p>';
}
	
function addtocart(pid){
	// read from local storage, create one if not exist
	var storage = localStorage.getItem('cart_storage');
	if(storage == undefined)
		storage = {};
	else storage = JSON.parse(storage);
	
	// initialize entry for newly added item
	if(storage[pid] == undefined)
		storage[pid] = 0;
	
	// increase quantity by 1, write it back to local storage
	storage[pid] = parseInt(storage[pid]) + 1;
	localStorage.setItem('cart_storage', JSON.stringify(storage));
	
	// refresh shopping cart
	refreshCart();
	alert("Product added to cart!");
}

function changeQuantity(pid){
	// read from local storage, create one if not exist
	var storage = localStorage.getItem('cart_storage');
	if(storage == undefined)
		return;
	else storage = JSON.parse(storage);
	
	// update quantity, delete entry if quantity <= 0, write it back to local storage
	var newValue = document.querySelector("#q_" + pid).value;
	if(newValue == "" || parseInt(newValue) <= 0){
		delete storage[pid];
	}else storage[pid] = parseInt(newValue);
	localStorage.setItem('cart_storage', JSON.stringify(storage));
	
	// refresh shopping cart
	refreshCart();
}

function cartSubmit(){
	// read from local storage, cancel form submission if not exist
	var storage = window.localStorage.getItem("cart_storage");
	if(storage == undefined)
		return false;
	else storage = JSON.parse(storage);
	
	var form = document.querySelector("#cart");
	
	// ajax part
	var xhr = (window.XMLHttpRequest)? new XMLHttpRequest()
			   : new ActiveXObject("Microsoft.XMLHTTP"), async=true;
	xhr.open("POST", "checkout-process.php", async);
	xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xhr.onreadystatechange = function(){
		if(xhr.readyState == 4 && xhr.status == 200){
			var resp = JSON.parse(xhr.responseText);
			// server generate digest and invoice number
			form.custom.value = resp.custom;
			form.invoice.value = resp.invoice;
			
			// include hidden input for shopping cart item
			for(var i = 0; i < resp.items.length; i++){
				var newItem = document.createElement("input");
				newItem.type = "hidden";
				newItem.name = "item_name_" + (i+1);
				newItem.value = resp.items[i][0].toString();
				var newItem2 = document.createElement("input");
				newItem2.type = "hidden";
				newItem2.name = "amount_" + (i+1);
				newItem2.value = resp.items[i][1].toString();
				var newItem3 = document.createElement("input");
				newItem3.type = "hidden";
				newItem3.name = "quantity_" + (i+1);
				newItem3.value = resp.items[i][2].toString();
				form.appendChild(newItem);
				form.appendChild(newItem2);
				form.appendChild(newItem3);
			}
			
			// send to paypal server
			form.submit(); 
		}
	}
	
	xhr.send('cart=' + JSON.stringify(storage));
	
	// clear the shopping cart
	for (var key in localStorage)
		localStorage.removeItem(key);
}
