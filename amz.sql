/*
Navicat MySQL Data Transfer

Source Server         : 192.168.10.33
Source Server Version : 50722
Source Host           : 192.168.10.33:3306
Source Database       : amz

Target Server Type    : MYSQL
Target Server Version : 50722
File Encoding         : 65001

Date: 2019-03-20 14:27:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for accounts
-- ----------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_name` varchar(50) DEFAULT NULL,
  `account_email` varchar(50) DEFAULT NULL,
  `account_sellerid` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `imap_host` varchar(50) DEFAULT NULL,
  `imap_ssl` varchar(50) DEFAULT NULL,
  `imap_port` varchar(50) DEFAULT NULL,
  `smtp_host` varchar(50) DEFAULT NULL,
  `smtp_ssl` varchar(50) DEFAULT NULL,
  `smtp_port` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `signature` text,
  `last_mail_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1149 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for amazon_orders
-- ----------------------------
DROP TABLE IF EXISTS `amazon_orders`;
CREATE TABLE `amazon_orders` (
  `SellerId` varchar(50) NOT NULL,
  `MarketPlaceId` varchar(50) NOT NULL,
  `AmazonOrderId` varchar(50) NOT NULL,
  `SellerOrderId` varchar(50) NOT NULL,
  `ApiDownloadDate` datetime NOT NULL,
  `PurchaseDate` datetime NOT NULL,
  `LastUpdateDate` datetime NOT NULL,
  `OrderStatus` varchar(50) NOT NULL,
  `FulfillmentChannel` varchar(50) NOT NULL,
  `SalesChannel` varchar(50) NOT NULL,
  `OrderChannel` varchar(50) NOT NULL,
  `ShipServiceLevel` varchar(50) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `AddressLine1` varchar(200) NOT NULL,
  `AddressLine2` varchar(200) NOT NULL,
  `AddressLine3` varchar(200) NOT NULL,
  `City` varchar(200) NOT NULL,
  `County` varchar(200) NOT NULL,
  `District` varchar(200) NOT NULL,
  `StateOrRegion` varchar(200) NOT NULL,
  `PostalCode` varchar(200) NOT NULL,
  `CountryCode` varchar(50) NOT NULL,
  `Phone` varchar(200) NOT NULL,
  `Amount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `CurrencyCode` varchar(50) NOT NULL,
  `NumberOfItemsShipped` varchar(50) NOT NULL,
  `NumberOfItemsUnshipped` varchar(50) NOT NULL,
  `PaymentMethod` varchar(50) NOT NULL,
  `BuyerName` varchar(100) NOT NULL,
  `BuyerEmail` varchar(200) NOT NULL,
  `ShipServiceLevelCategory` varchar(50) NOT NULL,
  `EarliestShipDate` varchar(50) NOT NULL,
  `LatestShipDate` varchar(50) NOT NULL,
  `EarliestDeliveryDate` varchar(50) NOT NULL,
  `LatestDeliveryDate` varchar(50) NOT NULL,
  `ImportToSap` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  PRIMARY KEY (`SellerId`,`MarketPlaceId`,`AmazonOrderId`),
  KEY `SellerId,AmazonOrderId` (`SellerId`,`AmazonOrderId`) USING BTREE,
  KEY `AmazonOrderId` (`AmazonOrderId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for amazon_orders_item
-- ----------------------------
DROP TABLE IF EXISTS `amazon_orders_item`;
CREATE TABLE `amazon_orders_item` (
  `SellerId` varchar(50) NOT NULL,
  `MarketPlaceId` varchar(50) NOT NULL,
  `AmazonOrderId` varchar(50) NOT NULL,
  `ASIN` varchar(50) NOT NULL,
  `SellerSKU` varchar(50) NOT NULL,
  `OrderItemId` varchar(50) NOT NULL,
  `Title` varchar(500) NOT NULL,
  `QuantityOrdered` varchar(50) NOT NULL,
  `QuantityShipped` varchar(50) NOT NULL,
  `GiftWrapLevel` varchar(500) NOT NULL,
  `GiftMessageText` text NOT NULL,
  `ItemPriceAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ItemPriceCurrencyCode` varchar(50) NOT NULL,
  `ShippingPriceAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ShippingPriceCurrencyCode` varchar(50) NOT NULL,
  `GiftWrapPriceAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `GiftWrapPriceCurrencyCode` varchar(50) NOT NULL,
  `ItemTaxAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ItemTaxCurrencyCode` varchar(50) NOT NULL,
  `ShippingTaxAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ShippingTaxCurrencyCode` varchar(50) NOT NULL,
  `GiftWrapTaxAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `GiftWrapTaxCurrencyCode` varchar(50) NOT NULL,
  `ShippingDiscountAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ShippingDiscountCurrencyCode` varchar(50) NOT NULL,
  `PromotionDiscountAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `PromotionDiscountCurrencyCode` varchar(50) NOT NULL,
  `PromotionIds` varchar(500) NOT NULL,
  `CODFeeAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `CODFeeCurrencyCode` varchar(50) NOT NULL,
  `CODFeeDiscountAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `CODFeeDiscountCurrencyCode` varchar(50) NOT NULL,
  PRIMARY KEY (`SellerId`,`MarketPlaceId`,`AmazonOrderId`,`OrderItemId`),
  KEY `orderid` (`AmazonOrderId`) USING BTREE,
  KEY `orderid_sellerid` (`SellerId`,`AmazonOrderId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for asin
-- ----------------------------
DROP TABLE IF EXISTS `asin`;
CREATE TABLE `asin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asin` varchar(50) DEFAULT NULL,
  `site` varchar(50) DEFAULT NULL,
  `sellersku` varchar(50) DEFAULT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `seller` varchar(50) DEFAULT NULL,
  `review_user_id` int(10) DEFAULT '0',
  `group_id` int(10) DEFAULT '0',
  `brand_line` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT '1',
  `star` decimal(2,1) unsigned zerofill DEFAULT '0.0',
  `item_model` varchar(50) DEFAULT NULL,
  `bg` varchar(50) DEFAULT NULL,
  `bu` varchar(50) DEFAULT NULL,
  `store` varchar(50) DEFAULT NULL,
  `item_group` varchar(30) DEFAULT NULL,
  `sap_seller_id` varchar(50) DEFAULT NULL,
  `sap_site_id` varchar(50) DEFAULT NULL,
  `sap_store_id` varchar(50) DEFAULT NULL,
  `sap_warehouse_id` varchar(50) DEFAULT NULL,
  `sap_factory_id` varchar(50) DEFAULT NULL,
  `sap_shipment_id` varchar(50) DEFAULT NULL,
  `asin_last_update_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `sales_28_22` int(10) unsigned DEFAULT '0',
  `sales_21_15` int(10) unsigned DEFAULT '0',
  `sales_14_08` int(10) unsigned DEFAULT '0',
  `sales_07_01` int(10) unsigned DEFAULT '0',
  `item_status` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `asin,site,sellersku` (`asin`,`site`,`sellersku`) USING BTREE,
  KEY `model` (`brand`,`brand_line`,`item_model`,`item_group`) USING BTREE,
  KEY `brand` (`brand`(10)),
  KEY `item_group` (`item_group`(10)),
  KEY `item_model` (`item_model`(10)),
  KEY `item_no` (`item_no`(10))
) ENGINE=MyISAM AUTO_INCREMENT=26261 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for asin_ads
-- ----------------------------
DROP TABLE IF EXISTS `asin_ads`;
CREATE TABLE `asin_ads` (
  `date_start` varchar(50) NOT NULL,
  `date_end` varchar(50) NOT NULL,
  `campaign_name` varchar(50) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `site` varchar(50) NOT NULL,
  `seller_code` varchar(50) NOT NULL,
  `fee_type` varchar(50) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `cost` varchar(50) DEFAULT NULL,
  `cost_base` varchar(50) DEFAULT NULL,
  `sales` varchar(50) DEFAULT NULL,
  `sales_base` varchar(50) DEFAULT NULL,
  `profit` varchar(50) DEFAULT NULL,
  `income` varchar(50) DEFAULT NULL,
  `roi` varchar(50) DEFAULT NULL,
  `acos` varchar(50) DEFAULT NULL,
  `exchange_rate` varchar(50) DEFAULT NULL,
  `currency` varchar(50) DEFAULT NULL,
  `currency_base` varchar(50) DEFAULT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `delete_tag` varchar(50) DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL,
  `time` varchar(50) DEFAULT NULL,
  `updated_at` varchar(50) DEFAULT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `d` (`date_start`,`date_end`,`campaign_name`,`sku`,`site`,`seller_code`,`fee_type`,`item_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=41985 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for asin_b
-- ----------------------------
DROP TABLE IF EXISTS `asin_b`;
CREATE TABLE `asin_b` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asin` varchar(50) DEFAULT NULL,
  `site` varchar(50) DEFAULT NULL,
  `sellersku` varchar(50) DEFAULT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `seller` varchar(50) DEFAULT NULL,
  `review_user_id` int(10) DEFAULT '0',
  `group_id` int(10) DEFAULT '0',
  `brand_line` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT '1',
  `star` decimal(2,1) unsigned zerofill DEFAULT '0.0',
  `item_model` varchar(50) DEFAULT NULL,
  `bg` varchar(50) DEFAULT NULL,
  `bu` varchar(50) DEFAULT NULL,
  `store` varchar(50) DEFAULT NULL,
  `item_group` varchar(30) DEFAULT NULL,
  `sap_seller_id` varchar(50) DEFAULT NULL,
  `sap_site_id` varchar(50) DEFAULT NULL,
  `sap_store_id` varchar(50) DEFAULT NULL,
  `sap_warehouse_id` varchar(50) DEFAULT NULL,
  `sap_factory_id` varchar(50) DEFAULT NULL,
  `sap_shipment_id` varchar(50) DEFAULT NULL,
  `asin_last_update_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asin,site,sellersku` (`asin`,`site`,`sellersku`) USING BTREE,
  KEY `model` (`brand`,`brand_line`,`item_model`,`item_group`) USING BTREE,
  KEY `brand` (`brand`(10)),
  KEY `item_group` (`item_group`(10)),
  KEY `item_model` (`item_model`(10)),
  KEY `item_no` (`item_no`(10))
) ENGINE=MyISAM AUTO_INCREMENT=23691 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for asin_profits
-- ----------------------------
DROP TABLE IF EXISTS `asin_profits`;
CREATE TABLE `asin_profits` (
  `item_code` varchar(50) NOT NULL,
  `date` varchar(50) NOT NULL,
  `seller_code` varchar(50) NOT NULL,
  `seller_name` varchar(200) DEFAULT NULL,
  `item_name` varchar(500) DEFAULT NULL,
  `sold_qty` varchar(50) DEFAULT NULL,
  `trans_qty` varchar(50) DEFAULT NULL,
  `buyer_shipping_fee` varchar(50) DEFAULT NULL,
  `buyer_shipping_fee_add` varchar(50) DEFAULT NULL,
  `insurance_fee` varchar(50) DEFAULT NULL,
  `shipping_insurance_fee` varchar(50) DEFAULT NULL,
  `income` varchar(50) DEFAULT NULL,
  `sell_tax` varchar(50) DEFAULT NULL,
  `cost` varchar(50) DEFAULT NULL,
  `cost_total` varchar(50) DEFAULT NULL,
  `platfrom_fee` varchar(50) DEFAULT NULL,
  `submit_fee` varchar(50) DEFAULT NULL,
  `trans_fee` varchar(50) DEFAULT NULL,
  `exchange_fee` varchar(50) DEFAULT NULL,
  `shipping_fee` varchar(50) DEFAULT NULL,
  `warehouse_operation_fee` varchar(50) DEFAULT NULL,
  `depreciation_fee` varchar(50) DEFAULT NULL,
  `fba_fee` varchar(50) DEFAULT NULL,
  `return_depreciation_fee` varchar(50) DEFAULT NULL,
  `promotion_fee` varchar(50) DEFAULT NULL,
  `sales_discounts` varchar(50) DEFAULT NULL,
  `sales_profits` varchar(50) DEFAULT NULL,
  `actual_cost` varchar(50) DEFAULT NULL,
  `abnormal_bill` varchar(50) DEFAULT NULL,
  `item_group` varchar(50) DEFAULT NULL,
  `item_group_des` varchar(200) DEFAULT NULL,
  `updated_at` varchar(50) DEFAULT NULL,
  `id` int(10) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `d` (`item_code`,`date`,`seller_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=153759 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for asin_seller_count
-- ----------------------------
DROP TABLE IF EXISTS `asin_seller_count`;
CREATE TABLE `asin_seller_count` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(50) DEFAULT NULL,
  `asin` varchar(50) DEFAULT NULL,
  `created_at` varchar(50) DEFAULT NULL,
  `updated_at` varchar(50) DEFAULT NULL,
  `seller_count` int(10) unsigned zerofill DEFAULT '0000000000',
  `status` varchar(50) DEFAULT NULL,
  `seller_count_updated_at` varchar(50) DEFAULT '0',
  `error_count` int(10) unsigned zerofill DEFAULT '0000000000',
  `seller` varchar(50) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site,asin` (`site`,`asin`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2253 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for asin_seller_details
-- ----------------------------
DROP TABLE IF EXISTS `asin_seller_details`;
CREATE TABLE `asin_seller_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` varchar(50) DEFAULT NULL,
  `seller_name` varchar(100) DEFAULT NULL,
  `asin_seller_count_id` int(10) unsigned DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=250773 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for auto
-- ----------------------------
DROP TABLE IF EXISTS `auto`;
CREATE TABLE `auto` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `priority` int(10) DEFAULT NULL,
  `rule_name` varchar(200) DEFAULT NULL,
  `subject` text,
  `to_email` text,
  `from_email` text,
  `users` varchar(200) DEFAULT NULL,
  `date_from` varchar(10) DEFAULT NULL,
  `date_to` varchar(10) DEFAULT NULL,
  `time_from` varchar(10) DEFAULT '0',
  `time_to` varchar(255) DEFAULT NULL,
  `weeks` varchar(20) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for aws_report
-- ----------------------------
DROP TABLE IF EXISTS `aws_report`;
CREATE TABLE `aws_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `marketplace_id` varchar(50) NOT NULL,
  `campaign_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ad_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` float NOT NULL DEFAULT '0',
  `sales` float NOT NULL DEFAULT '0',
  `profit` float NOT NULL DEFAULT '0',
  `orders` int(11) NOT NULL DEFAULT '0',
  `acos` decimal(11,4) NOT NULL DEFAULT '0.0000',
  `impressions` int(11) NOT NULL DEFAULT '0',
  `clicks` int(11) NOT NULL DEFAULT '0',
  `ctr` decimal(11,4) NOT NULL DEFAULT '0.0000',
  `cpc` float NOT NULL DEFAULT '0',
  `ad_conversion_rate` decimal(11,4) NOT NULL DEFAULT '0.0000',
  `default_bid` float NOT NULL DEFAULT '0',
  `date` date NOT NULL,
  `state` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT '0',
  `sku` varchar(255) DEFAULT '',
  `bg` varchar(255) DEFAULT '',
  `bu` varchar(255) DEFAULT '',
  `ImportToSap` tinyint(1) unsigned zerofill DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_date_index` (`seller_id`,`marketplace_id`,`campaign_name`,`ad_group`,`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=82874 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for aws_report_time
-- ----------------------------
DROP TABLE IF EXISTS `aws_report_time`;
CREATE TABLE `aws_report_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` varchar(50) NOT NULL,
  `marketplace_id` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_marketplace` (`seller_id`,`marketplace_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for b1g1
-- ----------------------------
DROP TABLE IF EXISTS `b1g1`;
CREATE TABLE `b1g1` (
  `commented` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '客户是否已留评',
  `processor` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'users.id 处理人、负责人',
  `status` varchar(25) NOT NULL DEFAULT '' COMMENT '状态',
  `steps` text COMMENT '分步处理数据',
  `order_id` char(19) NOT NULL DEFAULT '',
  `gift_sku` varchar(55) NOT NULL DEFAULT '',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT 'customer name',
  `email` varchar(55) NOT NULL DEFAULT '',
  `phone` varchar(55) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `rating` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '评论星级 (1 to 5)',
  `note` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  KEY `email` (`email`(20)),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
/*!50500 PARTITION BY RANGE  COLUMNS(created_at)
(PARTITION bygone VALUES LESS THAN ('2018-01-01') ENGINE = InnoDB,
 PARTITION `18:1-3` VALUES LESS THAN ('2018-04-01') ENGINE = InnoDB,
 PARTITION `18:4-6` VALUES LESS THAN ('2018-07-01') ENGINE = InnoDB,
 PARTITION `18:7-9` VALUES LESS THAN ('2018-10-01') ENGINE = InnoDB,
 PARTITION `18:10-12` VALUES LESS THAN ('2019-01-01') ENGINE = InnoDB,
 PARTITION `19:1-3` VALUES LESS THAN ('2019-04-01') ENGINE = InnoDB,
 PARTITION `19:4-6` VALUES LESS THAN ('2019-07-01') ENGINE = InnoDB,
 PARTITION `19:7-9` VALUES LESS THAN ('2019-10-01') ENGINE = InnoDB,
 PARTITION `19:10-12` VALUES LESS THAN ('2020-01-01') ENGINE = InnoDB,
 PARTITION `20:1-3` VALUES LESS THAN ('2020-04-01') ENGINE = InnoDB,
 PARTITION `20:4-6` VALUES LESS THAN ('2020-07-01') ENGINE = InnoDB,
 PARTITION `20:7-9` VALUES LESS THAN ('2020-10-01') ENGINE = InnoDB,
 PARTITION `20:10-12` VALUES LESS THAN ('2021-01-01') ENGINE = InnoDB,
 PARTITION `21:1-3` VALUES LESS THAN ('2021-04-01') ENGINE = InnoDB,
 PARTITION `21:4-6` VALUES LESS THAN ('2021-07-01') ENGINE = InnoDB,
 PARTITION `21:7-9` VALUES LESS THAN ('2021-10-01') ENGINE = InnoDB,
 PARTITION `21:10-12` VALUES LESS THAN ('2022-01-01') ENGINE = InnoDB,
 PARTITION `22:1-3` VALUES LESS THAN ('2022-04-01') ENGINE = InnoDB,
 PARTITION `22:4-6` VALUES LESS THAN ('2022-07-01') ENGINE = InnoDB,
 PARTITION `22:7-9` VALUES LESS THAN ('2022-10-01') ENGINE = InnoDB,
 PARTITION `22:10-12` VALUES LESS THAN ('2023-01-01') ENGINE = InnoDB,
 PARTITION `23:1-3` VALUES LESS THAN ('2023-04-01') ENGINE = InnoDB,
 PARTITION `23:4-6` VALUES LESS THAN ('2023-07-01') ENGINE = InnoDB,
 PARTITION `23:7-9` VALUES LESS THAN ('2023-10-01') ENGINE = InnoDB,
 PARTITION `23:10-12` VALUES LESS THAN ('2024-01-01') ENGINE = InnoDB,
 PARTITION `24:1-3` VALUES LESS THAN ('2024-04-01') ENGINE = InnoDB,
 PARTITION `24:4-6` VALUES LESS THAN ('2024-07-01') ENGINE = InnoDB,
 PARTITION `24:7-9` VALUES LESS THAN ('2024-10-01') ENGINE = InnoDB,
 PARTITION `24:10-12` VALUES LESS THAN ('2025-01-01') ENGINE = InnoDB,
 PARTITION `25:1-3` VALUES LESS THAN ('2025-04-01') ENGINE = InnoDB,
 PARTITION `25:4-6` VALUES LESS THAN ('2025-07-01') ENGINE = InnoDB,
 PARTITION `25:7-9` VALUES LESS THAN ('2025-10-01') ENGINE = InnoDB,
 PARTITION `25:10-12` VALUES LESS THAN ('2026-01-01') ENGINE = InnoDB,
 PARTITION future VALUES LESS THAN (MAXVALUE) ENGINE = InnoDB) */;

-- ----------------------------
-- Table structure for cashback
-- ----------------------------
DROP TABLE IF EXISTS `cashback`;
CREATE TABLE `cashback` (
  `commented` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '客户是否已留评',
  `processor` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'users.id 处理人、负责人',
  `status` varchar(25) NOT NULL DEFAULT '' COMMENT '状态',
  `steps` text COMMENT '分步处理数据',
  `order_id` char(19) NOT NULL DEFAULT '',
  `gift_sku` varchar(55) NOT NULL DEFAULT '',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT 'customer name',
  `email` varchar(55) NOT NULL DEFAULT '',
  `phone` varchar(55) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `rating` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '评论星级 (1 to 5)',
  `note` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  KEY `email` (`email`(20)),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
/*!50500 PARTITION BY RANGE  COLUMNS(created_at)
(PARTITION bygone VALUES LESS THAN ('2018-01-01') ENGINE = InnoDB,
 PARTITION `18:1-3` VALUES LESS THAN ('2018-04-01') ENGINE = InnoDB,
 PARTITION `18:4-6` VALUES LESS THAN ('2018-07-01') ENGINE = InnoDB,
 PARTITION `18:7-9` VALUES LESS THAN ('2018-10-01') ENGINE = InnoDB,
 PARTITION `18:10-12` VALUES LESS THAN ('2019-01-01') ENGINE = InnoDB,
 PARTITION `19:1-3` VALUES LESS THAN ('2019-04-01') ENGINE = InnoDB,
 PARTITION `19:4-6` VALUES LESS THAN ('2019-07-01') ENGINE = InnoDB,
 PARTITION `19:7-9` VALUES LESS THAN ('2019-10-01') ENGINE = InnoDB,
 PARTITION `19:10-12` VALUES LESS THAN ('2020-01-01') ENGINE = InnoDB,
 PARTITION `20:1-3` VALUES LESS THAN ('2020-04-01') ENGINE = InnoDB,
 PARTITION `20:4-6` VALUES LESS THAN ('2020-07-01') ENGINE = InnoDB,
 PARTITION `20:7-9` VALUES LESS THAN ('2020-10-01') ENGINE = InnoDB,
 PARTITION `20:10-12` VALUES LESS THAN ('2021-01-01') ENGINE = InnoDB,
 PARTITION `21:1-3` VALUES LESS THAN ('2021-04-01') ENGINE = InnoDB,
 PARTITION `21:4-6` VALUES LESS THAN ('2021-07-01') ENGINE = InnoDB,
 PARTITION `21:7-9` VALUES LESS THAN ('2021-10-01') ENGINE = InnoDB,
 PARTITION `21:10-12` VALUES LESS THAN ('2022-01-01') ENGINE = InnoDB,
 PARTITION `22:1-3` VALUES LESS THAN ('2022-04-01') ENGINE = InnoDB,
 PARTITION `22:4-6` VALUES LESS THAN ('2022-07-01') ENGINE = InnoDB,
 PARTITION `22:7-9` VALUES LESS THAN ('2022-10-01') ENGINE = InnoDB,
 PARTITION `22:10-12` VALUES LESS THAN ('2023-01-01') ENGINE = InnoDB,
 PARTITION `23:1-3` VALUES LESS THAN ('2023-04-01') ENGINE = InnoDB,
 PARTITION `23:4-6` VALUES LESS THAN ('2023-07-01') ENGINE = InnoDB,
 PARTITION `23:7-9` VALUES LESS THAN ('2023-10-01') ENGINE = InnoDB,
 PARTITION `23:10-12` VALUES LESS THAN ('2024-01-01') ENGINE = InnoDB,
 PARTITION `24:1-3` VALUES LESS THAN ('2024-04-01') ENGINE = InnoDB,
 PARTITION `24:4-6` VALUES LESS THAN ('2024-07-01') ENGINE = InnoDB,
 PARTITION `24:7-9` VALUES LESS THAN ('2024-10-01') ENGINE = InnoDB,
 PARTITION `24:10-12` VALUES LESS THAN ('2025-01-01') ENGINE = InnoDB,
 PARTITION `25:1-3` VALUES LESS THAN ('2025-04-01') ENGINE = InnoDB,
 PARTITION `25:4-6` VALUES LESS THAN ('2025-07-01') ENGINE = InnoDB,
 PARTITION `25:7-9` VALUES LESS THAN ('2025-10-01') ENGINE = InnoDB,
 PARTITION `25:10-12` VALUES LESS THAN ('2026-01-01') ENGINE = InnoDB,
 PARTITION future VALUES LESS THAN (MAXVALUE) ENGINE = InnoDB) */;

-- ----------------------------
-- Table structure for category
-- ----------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_pid` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_order` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for coupon_kunnr
-- ----------------------------
DROP TABLE IF EXISTS `coupon_kunnr`;
CREATE TABLE `coupon_kunnr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kunnr` varchar(255) DEFAULT NULL,
  `coupon_description` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `sap_seller_id` int(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `kunnr` (`kunnr`,`coupon_description`)
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for ctg
-- ----------------------------
DROP TABLE IF EXISTS `ctg`;
CREATE TABLE `ctg` (
  `commented` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '客户是否已留评',
  `processor` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'users.id 处理人、负责人',
  `status` varchar(25) NOT NULL DEFAULT '' COMMENT '状态',
  `steps` text COMMENT '分步处理数据',
  `order_id` char(19) NOT NULL DEFAULT '',
  `gift_sku` varchar(55) NOT NULL DEFAULT '',
  `name` varchar(55) NOT NULL DEFAULT '' COMMENT 'customer name',
  `email` varchar(55) NOT NULL DEFAULT '',
  `phone` varchar(55) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `rating` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '评论星级 (1 to 5)',
  `note` text NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  KEY `email` (`email`(20)),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
/*!50500 PARTITION BY RANGE  COLUMNS(created_at)
(PARTITION bygone VALUES LESS THAN ('2018-01-01') ENGINE = InnoDB,
 PARTITION `18:1-3` VALUES LESS THAN ('2018-04-01') ENGINE = InnoDB,
 PARTITION `18:4-6` VALUES LESS THAN ('2018-07-01') ENGINE = InnoDB,
 PARTITION `18:7-9` VALUES LESS THAN ('2018-10-01') ENGINE = InnoDB,
 PARTITION `18:10-12` VALUES LESS THAN ('2019-01-01') ENGINE = InnoDB,
 PARTITION `19:1-3` VALUES LESS THAN ('2019-04-01') ENGINE = InnoDB,
 PARTITION `19:4-6` VALUES LESS THAN ('2019-07-01') ENGINE = InnoDB,
 PARTITION `19:7-9` VALUES LESS THAN ('2019-10-01') ENGINE = InnoDB,
 PARTITION `19:10-12` VALUES LESS THAN ('2020-01-01') ENGINE = InnoDB,
 PARTITION `20:1-3` VALUES LESS THAN ('2020-04-01') ENGINE = InnoDB,
 PARTITION `20:4-6` VALUES LESS THAN ('2020-07-01') ENGINE = InnoDB,
 PARTITION `20:7-9` VALUES LESS THAN ('2020-10-01') ENGINE = InnoDB,
 PARTITION `20:10-12` VALUES LESS THAN ('2021-01-01') ENGINE = InnoDB,
 PARTITION `21:1-3` VALUES LESS THAN ('2021-04-01') ENGINE = InnoDB,
 PARTITION `21:4-6` VALUES LESS THAN ('2021-07-01') ENGINE = InnoDB,
 PARTITION `21:7-9` VALUES LESS THAN ('2021-10-01') ENGINE = InnoDB,
 PARTITION `21:10-12` VALUES LESS THAN ('2022-01-01') ENGINE = InnoDB,
 PARTITION `22:1-3` VALUES LESS THAN ('2022-04-01') ENGINE = InnoDB,
 PARTITION `22:4-6` VALUES LESS THAN ('2022-07-01') ENGINE = InnoDB,
 PARTITION `22:7-9` VALUES LESS THAN ('2022-10-01') ENGINE = InnoDB,
 PARTITION `22:10-12` VALUES LESS THAN ('2023-01-01') ENGINE = InnoDB,
 PARTITION `23:1-3` VALUES LESS THAN ('2023-04-01') ENGINE = InnoDB,
 PARTITION `23:4-6` VALUES LESS THAN ('2023-07-01') ENGINE = InnoDB,
 PARTITION `23:7-9` VALUES LESS THAN ('2023-10-01') ENGINE = InnoDB,
 PARTITION `23:10-12` VALUES LESS THAN ('2024-01-01') ENGINE = InnoDB,
 PARTITION `24:1-3` VALUES LESS THAN ('2024-04-01') ENGINE = InnoDB,
 PARTITION `24:4-6` VALUES LESS THAN ('2024-07-01') ENGINE = InnoDB,
 PARTITION `24:7-9` VALUES LESS THAN ('2024-10-01') ENGINE = InnoDB,
 PARTITION `24:10-12` VALUES LESS THAN ('2025-01-01') ENGINE = InnoDB,
 PARTITION `25:1-3` VALUES LESS THAN ('2025-04-01') ENGINE = InnoDB,
 PARTITION `25:4-6` VALUES LESS THAN ('2025-07-01') ENGINE = InnoDB,
 PARTITION `25:7-9` VALUES LESS THAN ('2025-10-01') ENGINE = InnoDB,
 PARTITION `25:10-12` VALUES LESS THAN ('2026-01-01') ENGINE = InnoDB,
 PARTITION future VALUES LESS THAN (MAXVALUE) ENGINE = InnoDB) */;

-- ----------------------------
-- Table structure for ctg_order
-- ----------------------------
DROP TABLE IF EXISTS `ctg_order`;
CREATE TABLE `ctg_order` (
  `SellerId` varchar(50) NOT NULL,
  `MarketPlaceId` varchar(50) NOT NULL,
  `AmazonOrderId` varchar(50) NOT NULL,
  `SellerOrderId` varchar(50) NOT NULL,
  `ApiDownloadDate` datetime NOT NULL,
  `PurchaseDate` datetime NOT NULL,
  `LastUpdateDate` datetime NOT NULL,
  `OrderStatus` varchar(50) NOT NULL,
  `FulfillmentChannel` varchar(50) NOT NULL,
  `SalesChannel` varchar(50) NOT NULL,
  `OrderChannel` varchar(50) NOT NULL,
  `ShipServiceLevel` varchar(50) NOT NULL,
  `Name` varchar(200) NOT NULL,
  `AddressLine1` varchar(200) NOT NULL,
  `AddressLine2` varchar(200) NOT NULL,
  `AddressLine3` varchar(200) NOT NULL,
  `City` varchar(200) NOT NULL,
  `County` varchar(200) NOT NULL,
  `District` varchar(200) NOT NULL,
  `StateOrRegion` varchar(200) NOT NULL,
  `PostalCode` varchar(200) NOT NULL,
  `CountryCode` varchar(50) NOT NULL,
  `Phone` varchar(200) NOT NULL,
  `Amount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `CurrencyCode` varchar(50) NOT NULL,
  `NumberOfItemsShipped` varchar(50) NOT NULL,
  `NumberOfItemsUnshipped` varchar(50) NOT NULL,
  `PaymentMethod` varchar(50) NOT NULL,
  `BuyerName` varchar(100) NOT NULL,
  `BuyerEmail` varchar(200) NOT NULL,
  `ShipServiceLevelCategory` varchar(50) NOT NULL,
  `EarliestShipDate` varchar(50) NOT NULL,
  `LatestShipDate` varchar(50) NOT NULL,
  `EarliestDeliveryDate` varchar(50) NOT NULL,
  `LatestDeliveryDate` varchar(50) NOT NULL,
  `ImportToSap` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  KEY `AmazonOrderId` (`AmazonOrderId`),
  KEY `SellerId` (`SellerId`),
  KEY `MarketPlaceId` (`MarketPlaceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
/*!50500 PARTITION BY RANGE  COLUMNS(created_at)
(PARTITION bygone VALUES LESS THAN ('2018-01-01') ENGINE = InnoDB,
 PARTITION `18:1-3` VALUES LESS THAN ('2018-04-01') ENGINE = InnoDB,
 PARTITION `18:4-6` VALUES LESS THAN ('2018-07-01') ENGINE = InnoDB,
 PARTITION `18:7-9` VALUES LESS THAN ('2018-10-01') ENGINE = InnoDB,
 PARTITION `18:10-12` VALUES LESS THAN ('2019-01-01') ENGINE = InnoDB,
 PARTITION `19:1-3` VALUES LESS THAN ('2019-04-01') ENGINE = InnoDB,
 PARTITION `19:4-6` VALUES LESS THAN ('2019-07-01') ENGINE = InnoDB,
 PARTITION `19:7-9` VALUES LESS THAN ('2019-10-01') ENGINE = InnoDB,
 PARTITION `19:10-12` VALUES LESS THAN ('2020-01-01') ENGINE = InnoDB,
 PARTITION `20:1-3` VALUES LESS THAN ('2020-04-01') ENGINE = InnoDB,
 PARTITION `20:4-6` VALUES LESS THAN ('2020-07-01') ENGINE = InnoDB,
 PARTITION `20:7-9` VALUES LESS THAN ('2020-10-01') ENGINE = InnoDB,
 PARTITION `20:10-12` VALUES LESS THAN ('2021-01-01') ENGINE = InnoDB,
 PARTITION `21:1-3` VALUES LESS THAN ('2021-04-01') ENGINE = InnoDB,
 PARTITION `21:4-6` VALUES LESS THAN ('2021-07-01') ENGINE = InnoDB,
 PARTITION `21:7-9` VALUES LESS THAN ('2021-10-01') ENGINE = InnoDB,
 PARTITION `21:10-12` VALUES LESS THAN ('2022-01-01') ENGINE = InnoDB,
 PARTITION `22:1-3` VALUES LESS THAN ('2022-04-01') ENGINE = InnoDB,
 PARTITION `22:4-6` VALUES LESS THAN ('2022-07-01') ENGINE = InnoDB,
 PARTITION `22:7-9` VALUES LESS THAN ('2022-10-01') ENGINE = InnoDB,
 PARTITION `22:10-12` VALUES LESS THAN ('2023-01-01') ENGINE = InnoDB,
 PARTITION `23:1-3` VALUES LESS THAN ('2023-04-01') ENGINE = InnoDB,
 PARTITION `23:4-6` VALUES LESS THAN ('2023-07-01') ENGINE = InnoDB,
 PARTITION `23:7-9` VALUES LESS THAN ('2023-10-01') ENGINE = InnoDB,
 PARTITION `23:10-12` VALUES LESS THAN ('2024-01-01') ENGINE = InnoDB,
 PARTITION `24:1-3` VALUES LESS THAN ('2024-04-01') ENGINE = InnoDB,
 PARTITION `24:4-6` VALUES LESS THAN ('2024-07-01') ENGINE = InnoDB,
 PARTITION `24:7-9` VALUES LESS THAN ('2024-10-01') ENGINE = InnoDB,
 PARTITION `24:10-12` VALUES LESS THAN ('2025-01-01') ENGINE = InnoDB,
 PARTITION `25:1-3` VALUES LESS THAN ('2025-04-01') ENGINE = InnoDB,
 PARTITION `25:4-6` VALUES LESS THAN ('2025-07-01') ENGINE = InnoDB,
 PARTITION `25:7-9` VALUES LESS THAN ('2025-10-01') ENGINE = InnoDB,
 PARTITION `25:10-12` VALUES LESS THAN ('2026-01-01') ENGINE = InnoDB,
 PARTITION future VALUES LESS THAN (MAXVALUE) ENGINE = InnoDB) */;

-- ----------------------------
-- Table structure for ctg_order_item
-- ----------------------------
DROP TABLE IF EXISTS `ctg_order_item`;
CREATE TABLE `ctg_order_item` (
  `SellerId` varchar(50) NOT NULL,
  `MarketPlaceId` varchar(50) NOT NULL,
  `MarketPlaceSite` varchar(50) NOT NULL,
  `AmazonOrderId` varchar(50) NOT NULL,
  `ASIN` varchar(50) NOT NULL,
  `SellerSKU` varchar(50) NOT NULL,
  `OrderItemId` varchar(50) NOT NULL,
  `Title` varchar(500) NOT NULL,
  `QuantityOrdered` varchar(50) NOT NULL,
  `QuantityShipped` varchar(50) NOT NULL,
  `GiftWrapLevel` varchar(500) NOT NULL,
  `GiftMessageText` text NOT NULL,
  `ItemPriceAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ItemPriceCurrencyCode` varchar(50) NOT NULL,
  `ShippingPriceAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ShippingPriceCurrencyCode` varchar(50) NOT NULL,
  `GiftWrapPriceAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `GiftWrapPriceCurrencyCode` varchar(50) NOT NULL,
  `ItemTaxAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ItemTaxCurrencyCode` varchar(50) NOT NULL,
  `ShippingTaxAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ShippingTaxCurrencyCode` varchar(50) NOT NULL,
  `GiftWrapTaxAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `GiftWrapTaxCurrencyCode` varchar(50) NOT NULL,
  `ShippingDiscountAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `ShippingDiscountCurrencyCode` varchar(50) NOT NULL,
  `PromotionDiscountAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `PromotionDiscountCurrencyCode` varchar(50) NOT NULL,
  `PromotionIds` varchar(500) NOT NULL,
  `CODFeeAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `CODFeeCurrencyCode` varchar(50) NOT NULL,
  `CODFeeDiscountAmount` decimal(19,6) NOT NULL DEFAULT '0.000000',
  `CODFeeDiscountCurrencyCode` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  KEY `SellerId` (`SellerId`),
  KEY `MarketPlaceId` (`MarketPlaceId`),
  KEY `AmazonOrderId` (`AmazonOrderId`),
  KEY `ASIN` (`ASIN`),
  KEY `SellerSKU` (`SellerSKU`),
  KEY `MarketPlaceSite` (`MarketPlaceSite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
/*!50500 PARTITION BY RANGE  COLUMNS(created_at)
(PARTITION bygone VALUES LESS THAN ('2018-01-01') ENGINE = InnoDB,
 PARTITION `18:1-3` VALUES LESS THAN ('2018-04-01') ENGINE = InnoDB,
 PARTITION `18:4-6` VALUES LESS THAN ('2018-07-01') ENGINE = InnoDB,
 PARTITION `18:7-9` VALUES LESS THAN ('2018-10-01') ENGINE = InnoDB,
 PARTITION `18:10-12` VALUES LESS THAN ('2019-01-01') ENGINE = InnoDB,
 PARTITION `19:1-3` VALUES LESS THAN ('2019-04-01') ENGINE = InnoDB,
 PARTITION `19:4-6` VALUES LESS THAN ('2019-07-01') ENGINE = InnoDB,
 PARTITION `19:7-9` VALUES LESS THAN ('2019-10-01') ENGINE = InnoDB,
 PARTITION `19:10-12` VALUES LESS THAN ('2020-01-01') ENGINE = InnoDB,
 PARTITION `20:1-3` VALUES LESS THAN ('2020-04-01') ENGINE = InnoDB,
 PARTITION `20:4-6` VALUES LESS THAN ('2020-07-01') ENGINE = InnoDB,
 PARTITION `20:7-9` VALUES LESS THAN ('2020-10-01') ENGINE = InnoDB,
 PARTITION `20:10-12` VALUES LESS THAN ('2021-01-01') ENGINE = InnoDB,
 PARTITION `21:1-3` VALUES LESS THAN ('2021-04-01') ENGINE = InnoDB,
 PARTITION `21:4-6` VALUES LESS THAN ('2021-07-01') ENGINE = InnoDB,
 PARTITION `21:7-9` VALUES LESS THAN ('2021-10-01') ENGINE = InnoDB,
 PARTITION `21:10-12` VALUES LESS THAN ('2022-01-01') ENGINE = InnoDB,
 PARTITION `22:1-3` VALUES LESS THAN ('2022-04-01') ENGINE = InnoDB,
 PARTITION `22:4-6` VALUES LESS THAN ('2022-07-01') ENGINE = InnoDB,
 PARTITION `22:7-9` VALUES LESS THAN ('2022-10-01') ENGINE = InnoDB,
 PARTITION `22:10-12` VALUES LESS THAN ('2023-01-01') ENGINE = InnoDB,
 PARTITION `23:1-3` VALUES LESS THAN ('2023-04-01') ENGINE = InnoDB,
 PARTITION `23:4-6` VALUES LESS THAN ('2023-07-01') ENGINE = InnoDB,
 PARTITION `23:7-9` VALUES LESS THAN ('2023-10-01') ENGINE = InnoDB,
 PARTITION `23:10-12` VALUES LESS THAN ('2024-01-01') ENGINE = InnoDB,
 PARTITION `24:1-3` VALUES LESS THAN ('2024-04-01') ENGINE = InnoDB,
 PARTITION `24:4-6` VALUES LESS THAN ('2024-07-01') ENGINE = InnoDB,
 PARTITION `24:7-9` VALUES LESS THAN ('2024-10-01') ENGINE = InnoDB,
 PARTITION `24:10-12` VALUES LESS THAN ('2025-01-01') ENGINE = InnoDB,
 PARTITION `25:1-3` VALUES LESS THAN ('2025-04-01') ENGINE = InnoDB,
 PARTITION `25:4-6` VALUES LESS THAN ('2025-07-01') ENGINE = InnoDB,
 PARTITION `25:7-9` VALUES LESS THAN ('2025-10-01') ENGINE = InnoDB,
 PARTITION `25:10-12` VALUES LESS THAN ('2026-01-01') ENGINE = InnoDB,
 PARTITION future VALUES LESS THAN (MAXVALUE) ENGINE = InnoDB) */;

-- ----------------------------
-- Table structure for customers
-- ----------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(200) DEFAULT NULL,
  `customer_id` varchar(200) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(200) DEFAULT NULL,
  `other` varchar(500) DEFAULT NULL,
  `last_update_date` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site` (`site`,`customer_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11018 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for exception
-- ----------------------------
DROP TABLE IF EXISTS `exception`;
CREATE TABLE `exception` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL,
  `sellerid` varchar(50) DEFAULT NULL,
  `process_date` varchar(50) DEFAULT NULL,
  `amazon_order_id` varchar(50) DEFAULT NULL,
  `refund` decimal(10,2) DEFAULT NULL,
  `gift_card_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '礼品卡金额',
  `replacement` text,
  `user_id` int(10) DEFAULT '0',
  `group_id` int(10) DEFAULT '0',
  `process_user_id` int(10) DEFAULT '0',
  `process_status` varchar(50) DEFAULT NULL,
  `request_content` text,
  `process_content` text,
  `order_sku` varchar(200) DEFAULT NULL,
  `process_attach` varchar(200) DEFAULT NULL,
  `replacement_order_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17772 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for fba_stock
-- ----------------------------
DROP TABLE IF EXISTS `fba_stock`;
CREATE TABLE `fba_stock` (
  `seller_id` varchar(50) NOT NULL,
  `seller_name` varchar(50) DEFAULT NULL,
  `asin` varchar(50) NOT NULL,
  `seller_sku` varchar(50) NOT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `fba_stock` int(10) DEFAULT NULL,
  `fba_transfer` int(10) DEFAULT NULL,
  `updated_at` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`seller_id`,`asin`,`seller_sku`),
  KEY `item_code` (`item_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for fbm_stock
-- ----------------------------
DROP TABLE IF EXISTS `fbm_stock`;
CREATE TABLE `fbm_stock` (
  `item_code` varchar(50) NOT NULL,
  `cost` decimal(10,2) unsigned DEFAULT '0.00',
  `fbm_stock` int(10) unsigned DEFAULT '0',
  `fbm_amount` decimal(10,2) unsigned DEFAULT '0.00',
  `updated_at` varchar(50) DEFAULT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`item_code`),
  UNIQUE KEY `item_code` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for group
-- ----------------------------
DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(200) DEFAULT NULL,
  `user_ids` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for group_detail
-- ----------------------------
DROP TABLE IF EXISTS `group_detail`;
CREATE TABLE `group_detail` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `group_id` int(10) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `time_from` varchar(10) DEFAULT NULL,
  `time_to` varchar(10) DEFAULT NULL,
  `leader` tinyint(1) unsigned zerofill DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4227 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for inbox
-- ----------------------------
DROP TABLE IF EXISTS `inbox`;
CREATE TABLE `inbox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mail_address` varchar(100) NOT NULL,
  `mail_id` varchar(150) NOT NULL,
  `from_name` varchar(200) DEFAULT NULL,
  `from_address` varchar(200) DEFAULT NULL,
  `to_address` varchar(200) DEFAULT NULL,
  `cc` varchar(1000) DEFAULT NULL,
  `bcc` varchar(1000) DEFAULT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `text_html` longtext,
  `text_plain` longtext,
  `date` varchar(50) DEFAULT NULL,
  `attachs` text,
  `user_id` int(10) DEFAULT NULL,
  `remark` varchar(1000) DEFAULT NULL,
  `amazon_order_id` varchar(50) DEFAULT NULL,
  `read` tinyint(1) unsigned zerofill DEFAULT '0',
  `reply` tinyint(1) unsigned zerofill DEFAULT '0',
  `rule_id` int(10) unsigned zerofill DEFAULT '0000000000',
  `auto_id` int(10) unsigned zerofill DEFAULT '0000000000',
  `warn` tinyint(1) unsigned zerofill DEFAULT '0',
  `etype` varchar(50) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `asin` varchar(50) DEFAULT NULL,
  `mark` varchar(50) DEFAULT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `epoint` varchar(200) DEFAULT NULL,
  `epoint_product` varchar(50) DEFAULT NULL,
  `epoint_group` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `amazon_seller_id` varchar(50) DEFAULT NULL,
  `group_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail_id,mail_address` (`mail_address`,`mail_id`) USING BTREE,
  KEY `from_address` (`from_address`) USING BTREE,
  KEY `to_address` (`to_address`) USING BTREE,
  KEY `sku` (`sku`) USING BTREE,
  KEY `etype` (`etype`) USING BTREE,
  KEY `mark` (`mark`) USING BTREE,
  FULLTEXT KEY `text_html` (`text_html`)
) ENGINE=MyISAM AUTO_INCREMENT=233702 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for inbox_change_log
-- ----------------------------
DROP TABLE IF EXISTS `inbox_change_log`;
CREATE TABLE `inbox_change_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL,
  `to_user_id` int(10) DEFAULT NULL,
  `inbox_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=112212 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for kms_learn
-- ----------------------------
DROP TABLE IF EXISTS `kms_learn`;
CREATE TABLE `kms_learn` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `brand` varchar(50) NOT NULL,
  `item_group` varchar(50) NOT NULL,
  `item_model` varchar(50) NOT NULL,
  `tags` varchar(2048) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_group` (`item_group`(10)) USING BTREE,
  KEY `item_model` (`item_model`(10)) USING BTREE,
  KEY `brand` (`brand`(10)) USING BTREE,
  FULLTEXT KEY `content` (`content`),
  FULLTEXT KEY `tags` (`tags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='KMS 学习中心';

-- ----------------------------
-- Table structure for kms_notice
-- ----------------------------
DROP TABLE IF EXISTS `kms_notice`;
CREATE TABLE `kms_notice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `brand` varchar(50) NOT NULL,
  `item_group` varchar(50) NOT NULL,
  `item_model` varchar(50) NOT NULL,
  `tags` varchar(2048) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_group` (`item_group`(10)) USING BTREE,
  KEY `item_model` (`item_model`(10)) USING BTREE,
  KEY `brand` (`brand`(10)) USING BTREE,
  FULLTEXT KEY `content` (`content`),
  FULLTEXT KEY `tags` (`tags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='KMS 公告中心';

-- ----------------------------
-- Table structure for kms_tag
-- ----------------------------
DROP TABLE IF EXISTS `kms_tag`;
CREATE TABLE `kms_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `tag` varchar(50) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tag` (`type`,`tag`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for kms_user_manual
-- ----------------------------
DROP TABLE IF EXISTS `kms_user_manual`;
CREATE TABLE `kms_user_manual` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) NOT NULL,
  `item_group` varchar(50) NOT NULL,
  `item_model` varchar(50) NOT NULL,
  `link` varchar(512) NOT NULL,
  `link_hash` char(32) NOT NULL COMMENT 'md5 去重',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `note` varchar(512) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `link_hash` (`link_hash`),
  KEY `item_group` (`item_group`(10)) USING BTREE,
  KEY `item_model` (`item_model`(10)) USING BTREE,
  KEY `brand` (`brand`(10)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for kms_video
-- ----------------------------
DROP TABLE IF EXISTS `kms_video`;
CREATE TABLE `kms_video` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `brand` varchar(50) NOT NULL,
  `item_group` varchar(50) NOT NULL,
  `item_model` varchar(50) NOT NULL,
  `type` enum('Others','Marketing Video','Buyers Video','Operation Instruction') NOT NULL DEFAULT 'Others',
  `descr` varchar(512) NOT NULL,
  `link` varchar(512) NOT NULL,
  `link_hash` char(32) NOT NULL COMMENT 'md5 去重',
  `note` varchar(512) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` varchar(25) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `link_hash` (`link_hash`),
  KEY `item_group` (`item_group`(10)) USING BTREE,
  KEY `item_model` (`item_model`(10)) USING BTREE,
  KEY `brand` (`brand`(10)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for knowledge_category
-- ----------------------------
DROP TABLE IF EXISTS `knowledge_category`;
CREATE TABLE `knowledge_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_pid` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_order` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for phone
-- ----------------------------
DROP TABLE IF EXISTS `phone`;
CREATE TABLE `phone` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seller_email` varchar(100) DEFAULT NULL,
  `buyer_email` varchar(100) DEFAULT NULL,
  `content` text,
  `phone` varchar(100) DEFAULT NULL,
  `seller_id` varchar(100) DEFAULT NULL,
  `amazon_order_id` varchar(100) DEFAULT NULL,
  `remark` varchar(1000) DEFAULT NULL,
  `etype` varchar(50) DEFAULT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `asin` varchar(50) DEFAULT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `epoint` varchar(200) DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3275 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for qa
-- ----------------------------
DROP TABLE IF EXISTS `qa`;
CREATE TABLE `qa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_line` varchar(200) DEFAULT NULL,
  `product` varchar(200) DEFAULT NULL,
  `item_no` varchar(200) DEFAULT NULL,
  `model` varchar(200) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `description` text,
  `user_id` int(10) DEFAULT NULL,
  `service_content` text,
  `dqe_content` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `confirm` tinyint(1) unsigned DEFAULT '0',
  `etype` varchar(50) DEFAULT NULL,
  `epoint` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=699 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for review
-- ----------------------------
DROP TABLE IF EXISTS `review`;
CREATE TABLE `review` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `seller_id` varchar(100) DEFAULT NULL,
  `site` varchar(50) DEFAULT NULL,
  `review` varchar(50) DEFAULT NULL,
  `date` varchar(10) DEFAULT NULL,
  `asin` varchar(50) DEFAULT NULL,
  `rating` tinyint(1) unsigned zerofill DEFAULT '0',
  `updated_rating` tinyint(1) unsigned zerofill DEFAULT '0',
  `reviewer_name` text,
  `review_content` text,
  `buyer_email` varchar(500) DEFAULT NULL,
  `edate` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `status` int(5) DEFAULT '1',
  `etype` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `amazon_order_id` varchar(50) DEFAULT NULL,
  `warn` tinyint(1) unsigned zerofill DEFAULT '0',
  `creson` varchar(200) DEFAULT NULL,
  `negative_value` int(10) unsigned zerofill NOT NULL DEFAULT '0000000000',
  `user_id` int(10) unsigned zerofill NOT NULL DEFAULT '0000000000',
  `buyer_phone` varchar(200) DEFAULT NULL,
  `follow_content` text,
  `follow_status` int(5) unsigned zerofill DEFAULT '00000',
  `customer_id` varchar(50) DEFAULT NULL,
  `vp` tinyint(1) unsigned zerofill DEFAULT '0',
  `title` varchar(500) DEFAULT NULL,
  `is_delete` tinyint(1) unsigned zerofill DEFAULT NULL,
  `nextdate` varchar(50) DEFAULT NULL,
  `customer_feedback` tinyint(1) unsigned zerofill DEFAULT '0',
  `updated_date` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site,review` (`site`,`review`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=198557 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for review_change_log
-- ----------------------------
DROP TABLE IF EXISTS `review_change_log`;
CREATE TABLE `review_change_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL,
  `date` varchar(50) DEFAULT NULL,
  `to_user_id` int(10) DEFAULT NULL,
  `review_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=56509 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for review_customers
-- ----------------------------
DROP TABLE IF EXISTS `review_customers`;
CREATE TABLE `review_customers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site` varchar(200) DEFAULT NULL,
  `review` varchar(50) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `phone` varchar(200) DEFAULT NULL,
  `other` varchar(500) DEFAULT NULL,
  `last_update_date` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site` (`site`,`review`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7106 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for review_step
-- ----------------------------
DROP TABLE IF EXISTS `review_step`;
CREATE TABLE `review_step` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for rsg_products
-- ----------------------------
DROP TABLE IF EXISTS `rsg_products`;
CREATE TABLE `rsg_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` varchar(50) DEFAULT NULL,
  `site` varchar(50) DEFAULT NULL,
  `asin` varchar(50) DEFAULT NULL,
  `end_date` varchar(10) DEFAULT NULL,
  `start_date` varchar(10) DEFAULT NULL,
  `product_name` varchar(500) DEFAULT NULL,
  `product_img` varchar(255) DEFAULT NULL,
  `daily_stock` int(10) unsigned zerofill DEFAULT '0000000000',
  `daily_remain` int(10) unsigned zerofill DEFAULT '0000000000',
  `price` decimal(10,2) unsigned zerofill DEFAULT '00000000.00',
  `currency` varchar(3) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `keyword` varchar(255) DEFAULT NULL,
  `position` int(10) unsigned zerofill DEFAULT '0000000000',
  `page` int(10) unsigned zerofill DEFAULT '0000000000',
  `positive_target` int(10) unsigned zerofill DEFAULT '0000000000',
  `positive_daily_limit` int(10) unsigned zerofill DEFAULT '0000000000',
  `user_id` int(10) unsigned zerofill DEFAULT '0000000000',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `review_rating` int(11) unsigned zerofill DEFAULT '00000000000',
  `number_of_reviews` int(11) unsigned zerofill DEFAULT '00000000000',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for rsg_requests
-- ----------------------------
DROP TABLE IF EXISTS `rsg_requests`;
CREATE TABLE `rsg_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned zerofill DEFAULT '0000000000',
  `customer_email` varchar(255) DEFAULT NULL,
  `step` tinyint(1) unsigned zerofill DEFAULT '0',
  `customer_paypal_email` varchar(255) DEFAULT NULL,
  `transfer_amount` decimal(10,2) DEFAULT NULL,
  `transfer_currency` varchar(3) DEFAULT NULL,
  `transfer_paypal_account` varchar(255) DEFAULT NULL,
  `transaction_id` text,
  `transfer_date` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `amazon_order_id` varchar(200) DEFAULT NULL,
  `review_url` text,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `user_id` int(10) unsigned zerofill DEFAULT '0000000000',
  `star_rating` varchar(255) DEFAULT NULL,
  `follow` varchar(255) DEFAULT NULL,
  `next_follow_date` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_email` (`customer_email`)
) ENGINE=InnoDB AUTO_INCREMENT=1260 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for rules
-- ----------------------------
DROP TABLE IF EXISTS `rules`;
CREATE TABLE `rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `priority` int(10) DEFAULT NULL,
  `rule_name` varchar(200) DEFAULT NULL,
  `subject` text,
  `to_email` text,
  `from_email` text,
  `asin` text,
  `sku` text,
  `timeout` varchar(10) DEFAULT NULL,
  `user_id` int(10) DEFAULT '0',
  `reply_status` tinyint(1) unsigned zerofill DEFAULT '0',
  `group_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for sales_28_day
-- ----------------------------
DROP TABLE IF EXISTS `sales_28_day`;
CREATE TABLE `sales_28_day` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seller_sku` varchar(50) DEFAULT NULL,
  `site_id` varchar(50) DEFAULT NULL,
  `qty` int(10) DEFAULT NULL,
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_sku` (`seller_sku`,`site_id`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=55350 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for sales_prediction
-- ----------------------------
DROP TABLE IF EXISTS `sales_prediction`;
CREATE TABLE `sales_prediction` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(100) DEFAULT NULL,
  `sku_des` text,
  `sap_site_id` int(10) unsigned DEFAULT '0',
  `item_group` varchar(100) DEFAULT NULL,
  `bg` varchar(50) DEFAULT NULL,
  `bu` varchar(50) DEFAULT NULL,
  `sap_seller_id` int(10) unsigned DEFAULT '0',
  `seller_skus` text,
  `sales_28_22` int(10) unsigned DEFAULT '0',
  `sales_21_15` int(10) unsigned DEFAULT '0',
  `sales_14_08` int(10) unsigned DEFAULT '0',
  `sales_07_01` int(10) unsigned DEFAULT '0',
  `date` date DEFAULT NULL,
  `week_sales` text,
  `status` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`,`sap_site_id`,`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=24325 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for sap_kunnr
-- ----------------------------
DROP TABLE IF EXISTS `sap_kunnr`;
CREATE TABLE `sap_kunnr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` varchar(50) DEFAULT NULL,
  `site` varchar(50) DEFAULT NULL,
  `kunnr` varchar(50) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_id` (`seller_id`,`site`)
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for seller_asins
-- ----------------------------
DROP TABLE IF EXISTS `seller_asins`;
CREATE TABLE `seller_asins` (
  `asin` varchar(50) NOT NULL,
  `marketplaceid` varchar(50) NOT NULL,
  `sales` decimal(10,2) unsigned zerofill NOT NULL,
  `total_sales` decimal(10,2) unsigned zerofill NOT NULL,
  `stock` int(10) unsigned zerofill NOT NULL,
  `updated_at` varchar(50) NOT NULL,
  `transfer` int(10) unsigned zerofill NOT NULL,
  `cost` decimal(10,2) unsigned zerofill NOT NULL,
  `fbm_stock` int(10) unsigned zerofill NOT NULL,
  `fbm_amount` decimal(10,2) unsigned zerofill NOT NULL,
  `item_name` text NOT NULL,
  `item_code` text NOT NULL,
  `total_star` int(10) unsigned zerofill NOT NULL,
  `avg_star` decimal(3,1) unsigned zerofill NOT NULL,
  `profits` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stock_keep` decimal(10,2) unsigned zerofill NOT NULL,
  `fba_stock_keep` decimal(10,2) unsigned zerofill NOT NULL,
  `stock_amount` decimal(10,2) unsigned zerofill NOT NULL,
  `positive_value` int(10) unsigned zerofill NOT NULL,
  `negative_value` int(10) unsigned zerofill NOT NULL,
  `site` varchar(50) NOT NULL,
  `profits_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `income_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`asin`,`marketplaceid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for seller_asins_rules
-- ----------------------------
DROP TABLE IF EXISTS `seller_asins_rules`;
CREATE TABLE `seller_asins_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tab` varchar(50) DEFAULT NULL,
  `tab_rules` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for sendbox
-- ----------------------------
DROP TABLE IF EXISTS `sendbox`;
CREATE TABLE `sendbox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inbox_id` int(10) DEFAULT NULL,
  `from_address` varchar(200) DEFAULT NULL,
  `to_address` varchar(1000) DEFAULT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `text_html` longtext,
  `date` varchar(50) DEFAULT NULL,
  `attachs` text,
  `user_id` int(10) DEFAULT NULL,
  `created_at` varchar(50) DEFAULT NULL,
  `updated_at` varchar(50) DEFAULT NULL,
  `send_date` varchar(50) DEFAULT NULL,
  `error` text,
  `status` varchar(10) DEFAULT 'Waiting',
  `warn` tinyint(1) unsigned zerofill DEFAULT '0',
  `error_count` tinyint(1) unsigned zerofill DEFAULT '0',
  `plan_date` int(10) unsigned zerofill DEFAULT '0000000000',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=286810 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for skus_week
-- ----------------------------
DROP TABLE IF EXISTS `skus_week`;
CREATE TABLE `skus_week` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asin` varchar(50) DEFAULT NULL,
  `site` varchar(50) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `fba_stock` int(10) DEFAULT NULL,
  `fbm_stock` int(10) DEFAULT NULL,
  `fba_transfer` int(10) DEFAULT NULL,
  `total_stock` int(10) DEFAULT NULL,
  `fba_keep` decimal(10,2) DEFAULT NULL,
  `total_keep` decimal(10,2) DEFAULT NULL,
  `weeks` varchar(10) DEFAULT NULL,
  `strategy` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`asin`,`site`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for skus_week_details
-- ----------------------------
DROP TABLE IF EXISTS `skus_week_details`;
CREATE TABLE `skus_week_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asin` varchar(255) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `weeks` varchar(255) DEFAULT NULL,
  `ranking` text,
  `rating` text,
  `review` text,
  `sales` text,
  `price` text,
  `flow` text,
  `conversion` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`asin`,`site`,`weeks`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for star
-- ----------------------------
DROP TABLE IF EXISTS `star`;
CREATE TABLE `star` (
  `asin` varchar(50) NOT NULL,
  `sellersku` varchar(50) DEFAULT NULL,
  `domain` varchar(50) NOT NULL,
  `one_star_number` varchar(50) DEFAULT NULL,
  `two_star_number` varchar(50) DEFAULT NULL,
  `three_star_number` varchar(50) DEFAULT NULL,
  `four_star_number` varchar(50) DEFAULT NULL,
  `five_star_number` varchar(50) DEFAULT NULL,
  `total_star_number` varchar(50) DEFAULT NULL,
  `average_score` varchar(50) DEFAULT NULL,
  `create_at` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`asin`,`domain`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for star_history
-- ----------------------------
DROP TABLE IF EXISTS `star_history`;
CREATE TABLE `star_history` (
  `asin` varchar(50) NOT NULL,
  `sellersku` varchar(50) DEFAULT NULL,
  `domain` varchar(50) NOT NULL,
  `one_star_number` varchar(50) DEFAULT NULL,
  `two_star_number` varchar(50) DEFAULT NULL,
  `three_star_number` varchar(50) DEFAULT NULL,
  `four_star_number` varchar(50) DEFAULT NULL,
  `five_star_number` varchar(50) DEFAULT NULL,
  `total_star_number` varchar(50) DEFAULT NULL,
  `average_score` varchar(50) DEFAULT NULL,
  `create_at` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`asin`,`domain`,`create_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for templates
-- ----------------------------
DROP TABLE IF EXISTS `templates`;
CREATE TABLE `templates` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tag` varchar(200) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `content` text,
  `user_id` int(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=993 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `admin` tinyint(1) unsigned zerofill NOT NULL DEFAULT '0',
  `sap_seller_id` int(10) unsigned NOT NULL DEFAULT '0',
  `seller_rules` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- View structure for kms_stock
-- ----------------------------
DROP VIEW IF EXISTS `kms_stock`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`127.0.0.1` SQL SECURITY DEFINER VIEW `kms_stock` AS select `fba_stock`.`seller_id` AS `seller_id`,`fba_stock`.`seller_name` AS `seller_name`,`fba_stock`.`seller_sku` AS `seller_sku`,`fba_stock`.`asin` AS `asin`,`fbm_stock`.`item_code` AS `item_code`,`fbm_stock`.`item_name` AS `item_name`,`fbm_stock`.`fbm_stock` AS `fbm_stock`,`fba_stock`.`fba_stock` AS `fba_stock`,`fba_stock`.`fba_transfer` AS `fba_transfer` from (`fbm_stock` left join `fba_stock` on((`fbm_stock`.`item_code` = `fba_stock`.`item_code`))) where (`fbm_stock`.`item_code` is not null) union select `fba_stock`.`seller_id` AS `seller_id`,`fba_stock`.`seller_name` AS `seller_name`,`fba_stock`.`seller_sku` AS `seller_sku`,`fba_stock`.`asin` AS `asin`,`fba_stock`.`item_code` AS `item_code`,`fbm_stock`.`item_name` AS `item_name`,`fbm_stock`.`fbm_stock` AS `fbm_stock`,`fba_stock`.`fba_stock` AS `fba_stock`,`fba_stock`.`fba_transfer` AS `fba_transfer` from (`fba_stock` left join `fbm_stock` on((`fbm_stock`.`item_code` = `fba_stock`.`item_code`))) where (`fba_stock`.`item_code` is not null) ;
