CREATE TABLE IF NOT EXISTS `products` (
`product_id` int(11) NOT NULL AUTO_INCREMENT,
`product_name` varchar(100) NOT NULL,
`product_category` varchar(100) NOT NULL,
`product_description` varchar(255) NOT NULL,
`product_image` varchar(255) NOT NULL,
`product_image2` varchar(255) NOT NULL, 
`product image3` varchar(255) NOT NULL,
`product_image4` varchar(255) NOT NULL,
`product_price` decimal (6,2) NOT NULL, /*9999.99*/
`product_special_offer` integer(2) NOT NULL,
`product_color` varchar(100) NOT NULL,
PRIMARY KEY (`product_id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `orders` (
`order_id` int(11) NOT NULL AUTO_INCREMENT,
`order_cost` decimal(6,2) NOT NULL,
`order_status` varchar(100) NOT NULL DEFAULT 'on_hold',
`user_id` int(11) NOT NULL,
`user_phone` int(11) NOT NULL,
`user_city` varchar(255) NOT NULL,
`user_address` varchar(255) NOT NULL,
`order_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`order_id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `order_items` (
`item_id` int(11) NOT NULL AUTO_INCREMENT,
`order_id` int(11) NOT NULL,
`product_id` varchar(255) NOT NULL,
`product_name` varchar(255) NOT NULL,
`product_image` varchar(255) NOT NULL,
`user_id` int(11) NOT NULL,
`order_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`item_id`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `users` (
`user_id` int(11) NOT NULL AUTO_INCREMENT,
`user_name` varchar(100) NOT NULL,
`user_email` varchar(100) NOT NULL,
`user_password` varchar(100) NOT NULL,
 PRIMARY KEY (`user_id`),
 UNIQUE KEY `UX Constraint` (`user_email`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1;


/* Inserting product into the database */
INSERT INTO `products`(`product_name`,`product_category`,`product_description`,`product_image`,`product_image2`,`product_image3`,`product_image4`,`product_price`,`product_special_offer`,`product_color`)VALUES('Aprilia Bike' , 'Bike','Sports Bike', 'p2.jpg', 'p2.jpg', 'p2.jpg', 'p2.jpg', 155,0, 'Black');