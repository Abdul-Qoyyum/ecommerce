CREATE TABLE users(
   `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `username` varchar(255) NOT NULL UNIQUE KEY,
  `email` varchar(255) NOT NULL UNIQUE KEY,
  `password` varchar(255) NOT NULL,
  `password_reset_hash` varchar(64) DEFAULT NULL,
  `password_reset_exp` datetime DEFAULT NULL,
  `activation_hash` varchar(64) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `is_admin`  TINYINT NOT NULL DEFAULT '0'
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `employees`(
  `id` INT(11) NOT NULL,
  `fname` varchar(30) NOT NULL,
  `lname` varchar(30) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `password_reset_hash` varchar(64) DEFAULT NULL,
  `password_reset_exp` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `is_employee` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  FOREIGN KEY(`admin_id`) REFERENCES users(id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `remembered_logins` (
  `tokens_id` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE products(
id INT NOT NULL AUTO_INCREMENT,
name VARCHAR(100) NOT NULL,
products_id INT NOT NULL,
price DECIMAL(8,2) NOT NULL,
public_id VARCHAR(255) NOT NULL,
sku INT NOT NULL DEFAULT 0,
category_id INT,
thumbnail VARCHAR(200) NOT NULL,
description VARCHAR(200) NOT NULL,
UNIQUE KEY(products_id),
FOREIGN KEY(category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE CASCADE,
PRIMARY KEY(id),
created_at TIMESTAMP DEFAULT NOW() ON UPDATE CURRENT_TIMESTAMP
)ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE admin_products(
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  products_id INT NOT NULL,
  admin_id INT NOT NULL,
  FOREIGN KEY(products_id) REFERENCES products(products_id),
  FOREIGN KEY(admin_id) REFERENCES users(id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE categories(
id INT NOT NULL AUTO_INCREMENT,
product_category VARCHAR(100) NOT NULL UNIQUE KEY,
created_at TIMESTAMP DEFAULT NOW() ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY(id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE invitations(
  admin_id INT NOT NULL,
  tokens_id VARCHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  UNIQUE KEY(tokens_id),
  FOREIGN KEY(admin_id) REFERENCES users(id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE transactions(
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	product_id INT NOT NULL,
  admin_id INT NOT NULL,
	transaction_ref CHAR(20) NOT NULL,
  delivered_by VARCHAR(255),
  is_delivered INT NOT NULL DEFAULT 0,
  amount INT NOT NULL,
  quantity INT NOT NULL,
  payment_status TINYINT DEFAULT '0',
  created_at TIMESTAMP DEFAULT NOW() ON UPDATE CURRENT_TIMESTAMP,
	FOREIGN KEY(user_id) REFERENCES users(id),
  FOREIGN KEY(admin_id) REFERENCES users(id),
	FOREIGN KEY(product_id) REFERENCES products(id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE reviews(
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  products_id INT NOT NULL,
  rating INT NOT NULL,
  description VARCHAR(255) NOT NULL,
  UNIQUE KEY(user_id, products_id),
  FOREIGN KEY(products_id) REFERENCES products(id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
