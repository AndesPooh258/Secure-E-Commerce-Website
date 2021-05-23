# Secure-E-Commerce-Website
The course project for IERG4210 Web Programming and Security, which is a E-commerce website that support secure checkout flow with PayPal sandbox. In addition, countermeasures against common attacks, as well as techniques related to performance and search engine optimization are implemented.

## File:
1. README.md
	- A file to describe general information
2. IERG4210 Assignment Report.pdf
	- A file to briefly describe the functionality of this website and the work done during peer hacking phase. 
3. /html
	- A folder storing all public files
4. /config
	- A folder storing all configuration files
4. cart.db, orders.db
	- The database files used in this website

## Deploying Procedure
1. Create an AWS EC2 instance and connect to the instance</li>
2. Install Apache and PHP</li>
3. Configure file permissions</li>
4. Upload the file on /var/www</li>
- Reference: https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/install-LAMP.html

## Basic Features
1. Login Page: support user sign up and login</li>
2. Main Page and Category Page: display table-less product list</li>
3. Product Page: provide product details such as name, description and price</li>
4. Admin Page: allow manage products in DB and view transaction records</li>
5. AJAX Shopping List: support checkout function with PayPal</li>
</ol>

## Security Features
1. Server setup with secure configuration</li>
2. SSL certificate for https website</li>
3. Secure authentication for admin and user</li>
4. Proper and vigorous input validations and sanitizations</li>
5. Proper and vigorous context-dependent output sanitizations</li>
6. Proper use of prepared statements for every SQL call</li>
7. Validate a hidden nonce with every form</li>
8. Rotate session id upon successful login</li>
9. Authenticity validation for Instant Payment Notification</li>

## Performance Optimization
1. Minimizing payload size by minifying JavaScript and CSS</li>
2. Actively help the browser on website rendering</li>
3. Apply micro-optimization tricks for JavaScript</li>

## Search Engine Optimization
1. Setting appropriate meta information</li>
2. Optimizing content by better anchor text, use of images and header tags</li>
3. Make effective use of robots.txt</li>

Project website: https://secure.s73.ierg4210.ie.cuhk.edu.hk

© 2021 Andes Kei