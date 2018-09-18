/*
Navicat MySQL Data Transfer

Source Server         : 192.168.10.33
Source Server Version : 50722
Source Host           : 192.168.10.33:3306
Source Database       : amz

Target Server Type    : MYSQL
Target Server Version : 50722
File Encoding         : 65001

Date: 2018-09-17 14:26:00
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `accounts`
-- ----------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
`id`  int(10) UNSIGNED NOT NULL ,
`account_name`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`account_email`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`account_sellerid`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`email`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`password`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`imap_host`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`imap_ssl`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`imap_port`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`smtp_host`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`smtp_ssl`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`smtp_port`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`type`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `amazon_orders`
-- ----------------------------
DROP TABLE IF EXISTS `amazon_orders`;
CREATE TABLE `amazon_orders` (
`SellerId`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`MarketPlaceId`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`AmazonOrderId`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`SellerOrderId`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ApiDownloadDate`  datetime NOT NULL ,
`PurchaseDate`  datetime NOT NULL ,
`LastUpdateDate`  datetime NOT NULL ,
`OrderStatus`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`FulfillmentChannel`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`SalesChannel`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`OrderChannel`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ShipServiceLevel`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`Name`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`AddressLine1`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`AddressLine2`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`AddressLine3`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`City`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`County`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`District`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`StateOrRegion`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`PostalCode`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`CountryCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`Phone`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`Amount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`CurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`NumberOfItemsShipped`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`NumberOfItemsUnshipped`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`PaymentMethod`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`BuyerName`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`BuyerEmail`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ShipServiceLevelCategory`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`EarliestShipDate`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`LatestShipDate`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`EarliestDeliveryDate`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`LatestDeliveryDate`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ImportToSap`  tinyint(1) UNSIGNED ZEROFILL NOT NULL DEFAULT 0 ,
PRIMARY KEY (`SellerId`, `MarketPlaceId`, `AmazonOrderId`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `amazon_orders_item`
-- ----------------------------
DROP TABLE IF EXISTS `amazon_orders_item`;
CREATE TABLE `amazon_orders_item` (
`SellerId`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`MarketPlaceId`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`AmazonOrderId`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ASIN`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`SellerSKU`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`OrderItemId`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`Title`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`QuantityOrdered`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`QuantityShipped`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`GiftWrapLevel`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`GiftMessageText`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ItemPriceAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`ItemPriceCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ShippingPriceAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`ShippingPriceCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`GiftWrapPriceAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`GiftWrapPriceCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ItemTaxAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`ItemTaxCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ShippingTaxAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`ShippingTaxCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`GiftWrapTaxAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`GiftWrapTaxCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`ShippingDiscountAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`ShippingDiscountCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`PromotionDiscountAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`PromotionDiscountCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`PromotionIds`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`CODFeeAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`CODFeeCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`CODFeeDiscountAmount`  decimal(19,6) NOT NULL DEFAULT 0.000000 ,
`CODFeeDiscountCurrencyCode`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
PRIMARY KEY (`SellerId`, `MarketPlaceId`, `AmazonOrderId`, `OrderItemId`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `asin`
-- ----------------------------
DROP TABLE IF EXISTS `asin`;
CREATE TABLE `asin` (
`item_no`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`asin`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`site`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`brand`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`seller`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`review_user_id`  int(10) NULL DEFAULT 0 ,
`group_id`  int(10) NULL DEFAULT 0 ,
`brand_line`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`id`  int(10) UNSIGNED NOT NULL ,
`status`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '1' ,
`sellersku`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`star`  decimal(2,1) UNSIGNED ZEROFILL NULL DEFAULT 0.0 ,
`item_model`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`bg`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`bu`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`store`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`item_group`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `asin_ads`
-- ----------------------------
DROP TABLE IF EXISTS `asin_ads`;
CREATE TABLE `asin_ads` (
`date_start`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`date_end`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`campaign_name`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`sku`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`site`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`seller_code`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`fee_type`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`item_code`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`cost`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`cost_base`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sales`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sales_base`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`profit`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`income`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`roi`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`acos`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`exchange_rate`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`currency`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`currency_base`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`user_name`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`delete_tag`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`time`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`updated_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`id`  int(10) NOT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `asin_profits`
-- ----------------------------
DROP TABLE IF EXISTS `asin_profits`;
CREATE TABLE `asin_profits` (
`item_code`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`seller_code`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`seller_name`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`item_name`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sold_qty`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`trans_qty`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`buyer_shipping_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`buyer_shipping_fee_add`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`insurance_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`shipping_insurance_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`income`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sell_tax`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`cost`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`cost_total`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`platfrom_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`submit_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`trans_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`exchange_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`shipping_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`warehouse_operation_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`depreciation_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`fba_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`return_depreciation_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`promotion_fee`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sales_discounts`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sales_profits`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`actual_cost`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`abnormal_bill`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`item_group`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`item_group_des`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`updated_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`id`  int(10) NOT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `asin_seller_count`
-- ----------------------------
DROP TABLE IF EXISTS `asin_seller_count`;
CREATE TABLE `asin_seller_count` (
`id`  int(10) UNSIGNED NOT NULL ,
`site`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`asin`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`created_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`updated_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`seller_count`  int(10) UNSIGNED ZEROFILL NULL DEFAULT 0000000000 ,
`status`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`seller_count_updated_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' ,
`error_count`  int(10) UNSIGNED ZEROFILL NULL DEFAULT 0000000000 ,
`seller`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`title`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `asin_seller_details`
-- ----------------------------
DROP TABLE IF EXISTS `asin_seller_details`;
CREATE TABLE `asin_seller_details` (
`id`  int(10) UNSIGNED NOT NULL ,
`seller_id`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`seller_name`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`asin_seller_count_id`  int(10) UNSIGNED NULL DEFAULT NULL ,
`date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `auto`
-- ----------------------------
DROP TABLE IF EXISTS `auto`;
CREATE TABLE `auto` (
`id`  int(10) UNSIGNED NOT NULL ,
`priority`  int(10) NULL DEFAULT NULL ,
`rule_name`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`subject`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`to_email`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`from_email`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`users`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`date_from`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`date_to`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`time_from`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' ,
`time_to`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`weeks`  varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `bc`
-- ----------------------------
DROP TABLE IF EXISTS `bc`;
CREATE TABLE `bc` (
`a`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`b`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`c`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
PRIMARY KEY (`a`, `b`, `c`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `customers`
-- ----------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
`id`  int(10) UNSIGNED NOT NULL ,
`site`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`customer_id`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`email`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`phone`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`other`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`last_update_date`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `exception`
-- ----------------------------
DROP TABLE IF EXISTS `exception`;
CREATE TABLE `exception` (
`id`  int(10) UNSIGNED NOT NULL ,
`type`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`name`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sellerid`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`process_date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`amazon_order_id`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`refund`  decimal(10,2) NULL DEFAULT NULL ,
`replacement`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`user_id`  int(10) NULL DEFAULT 0 ,
`group_id`  int(10) NULL DEFAULT 0 ,
`process_user_id`  int(10) NULL DEFAULT 0 ,
`process_status`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`request_content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`process_content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`order_sku`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`process_attach`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `fbm_stock`
-- ----------------------------
DROP TABLE IF EXISTS `fbm_stock`;
CREATE TABLE `fbm_stock` (
`item_code`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`cost`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`fbm_stock`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`fbm_amount`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`updated_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`item_name`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`item_code`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `group`
-- ----------------------------
DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
`id`  int(10) NOT NULL ,
`group_name`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`user_ids`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `group_detail`
-- ----------------------------
DROP TABLE IF EXISTS `group_detail`;
CREATE TABLE `group_detail` (
`id`  int(10) NOT NULL ,
`group_id`  int(10) NULL DEFAULT NULL ,
`user_id`  int(10) NULL DEFAULT NULL ,
`time_from`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`time_to`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`leader`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `inbox`
-- ----------------------------
DROP TABLE IF EXISTS `inbox`;
CREATE TABLE `inbox` (
`id`  int(10) UNSIGNED NOT NULL ,
`mail_address`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`mail_id`  int(11) NOT NULL ,
`from_name`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`from_address`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`to_address`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`cc`  varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`bcc`  varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`subject`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`text_html`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`text_plain`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`attachs`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`user_id`  int(10) NULL DEFAULT NULL ,
`remark`  varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`amazon_order_id`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`read`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`reply`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`rule_id`  int(10) UNSIGNED ZEROFILL NULL DEFAULT 0000000000 ,
`auto_id`  int(10) UNSIGNED ZEROFILL NULL DEFAULT 0000000000 ,
`warn`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`etype`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sku`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`asin`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`mark`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`item_no`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`epoint`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`type`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`amazon_seller_id`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`group_id`  int(10) NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `inbox_change_log`
-- ----------------------------
DROP TABLE IF EXISTS `inbox_change_log`;
CREATE TABLE `inbox_change_log` (
`id`  int(10) UNSIGNED NOT NULL ,
`user_id`  int(10) NULL DEFAULT NULL ,
`date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`to_user_id`  int(10) NULL DEFAULT NULL ,
`inbox_id`  int(10) NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `password_resets`
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
`email`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL ,
`token`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL ,
`created_at`  timestamp NULL DEFAULT NULL 
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `phone`
-- ----------------------------
DROP TABLE IF EXISTS `phone`;
CREATE TABLE `phone` (
`id`  int(10) UNSIGNED NOT NULL ,
`seller_email`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`buyer_email`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`phone`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`seller_id`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`amazon_order_id`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`remark`  varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`etype`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sku`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`asin`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`item_no`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`epoint`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`user_id`  int(10) NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `qa`
-- ----------------------------
DROP TABLE IF EXISTS `qa`;
CREATE TABLE `qa` (
`id`  int(10) UNSIGNED NOT NULL ,
`product_line`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`product`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`item_no`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`model`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`title`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`description`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`user_id`  int(10) NULL DEFAULT NULL ,
`service_content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`dqe_content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`created_at`  timestamp NULL DEFAULT NULL ,
`updated_at`  timestamp NULL DEFAULT NULL ,
`confirm`  tinyint(1) UNSIGNED NULL DEFAULT 0 ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `review`
-- ----------------------------
DROP TABLE IF EXISTS `review`;
CREATE TABLE `review` (
`id`  int(10) NOT NULL ,
`seller_id`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`site`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`review`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`date`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`asin`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`rating`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`updated_rating`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`reviewer_name`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`review_content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`buyer_email`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`edate`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`created_at`  timestamp NULL DEFAULT NULL ,
`status`  int(5) NULL DEFAULT 1 ,
`etype`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`updated_at`  timestamp NULL DEFAULT NULL ,
`amazon_order_id`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`warn`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`creson`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`negative_value`  int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT 0000000000 ,
`user_id`  int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT 0000000000 ,
`buyer_phone`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`follow_content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`follow_status`  int(5) UNSIGNED ZEROFILL NULL DEFAULT 00000 ,
`customer_id`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`vp`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`title`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`is_delete`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `review_bak`
-- ----------------------------
DROP TABLE IF EXISTS `review_bak`;
CREATE TABLE `review_bak` (
`id`  int(10) NOT NULL ,
`seller_id`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`site`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`review`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`date`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`amazon_account`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`sellersku`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`asin`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`rating`  tinyint(1) NULL DEFAULT NULL ,
`reviewer_name`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`review_content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`buyer_email`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`edate`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`created_at`  timestamp NULL DEFAULT NULL ,
`content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`status`  int(5) NULL DEFAULT 1 ,
`etype`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`epoint`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`edescription`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`updated_at`  timestamp NULL DEFAULT NULL ,
`asin_url`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`amazon_order_id`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`warn`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`creson`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`negative_value`  int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT 0000000000 ,
`user_id`  int(10) UNSIGNED ZEROFILL NOT NULL DEFAULT 0000000000 ,
`buyer_phone`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`follow_content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`follow_status`  int(5) UNSIGNED ZEROFILL NULL DEFAULT 00000 ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `review_change_log`
-- ----------------------------
DROP TABLE IF EXISTS `review_change_log`;
CREATE TABLE `review_change_log` (
`id`  int(10) UNSIGNED NOT NULL ,
`user_id`  int(10) NULL DEFAULT NULL ,
`date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`to_user_id`  int(10) NULL DEFAULT NULL ,
`review_id`  int(10) NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `review_step`
-- ----------------------------
DROP TABLE IF EXISTS `review_step`;
CREATE TABLE `review_step` (
`id`  int(10) UNSIGNED NOT NULL ,
`title`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `rules`
-- ----------------------------
DROP TABLE IF EXISTS `rules`;
CREATE TABLE `rules` (
`id`  int(10) UNSIGNED NOT NULL ,
`priority`  int(10) NULL DEFAULT NULL ,
`rule_name`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`subject`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`to_email`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`from_email`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`asin`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`sku`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`timeout`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`user_id`  int(10) NULL DEFAULT 0 ,
`reply_status`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`group_id`  int(10) NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `seller_asins`
-- ----------------------------
DROP TABLE IF EXISTS `seller_asins`;
CREATE TABLE `seller_asins` (
`asin`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`marketplaceid`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`sales`  decimal(10,2) UNSIGNED ZEROFILL NOT NULL ,
`total_sales`  decimal(10,2) UNSIGNED ZEROFILL NOT NULL ,
`stock`  int(10) UNSIGNED ZEROFILL NOT NULL ,
`updated_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`transfer`  int(10) UNSIGNED ZEROFILL NOT NULL ,
`cost`  decimal(10,2) UNSIGNED ZEROFILL NOT NULL ,
`fbm_stock`  int(10) UNSIGNED ZEROFILL NOT NULL ,
`fbm_amount`  decimal(10,2) UNSIGNED ZEROFILL NOT NULL ,
`item_name`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`item_code`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`total_star`  int(10) UNSIGNED ZEROFILL NOT NULL ,
`avg_star`  decimal(3,1) UNSIGNED ZEROFILL NOT NULL ,
`profits`  decimal(10,2) NOT NULL DEFAULT 0.00 ,
`stock_keep`  decimal(10,2) UNSIGNED ZEROFILL NOT NULL ,
`fba_stock_keep`  decimal(10,2) UNSIGNED ZEROFILL NOT NULL ,
`stock_amount`  decimal(10,2) UNSIGNED ZEROFILL NOT NULL ,
`positive_value`  int(10) UNSIGNED ZEROFILL NOT NULL ,
`negative_value`  int(10) UNSIGNED ZEROFILL NOT NULL ,
`site`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
PRIMARY KEY (`asin`, `marketplaceid`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `seller_asins_rules`
-- ----------------------------
DROP TABLE IF EXISTS `seller_asins_rules`;
CREATE TABLE `seller_asins_rules` (
`id`  int(10) UNSIGNED NOT NULL ,
`tab`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`tab_rules`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `sendbox`
-- ----------------------------
DROP TABLE IF EXISTS `sendbox`;
CREATE TABLE `sendbox` (
`id`  int(10) UNSIGNED NOT NULL ,
`inbox_id`  int(10) NULL DEFAULT NULL ,
`from_address`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`to_address`  varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`subject`  varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`text_html`  longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`attachs`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`user_id`  int(10) NULL DEFAULT NULL ,
`created_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`updated_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`send_date`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`error`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`status`  varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'Waiting' ,
`warn`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
`error_count`  tinyint(1) UNSIGNED ZEROFILL NULL DEFAULT 0 ,
PRIMARY KEY (`id`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `star`
-- ----------------------------
DROP TABLE IF EXISTS `star`;
CREATE TABLE `star` (
`asin`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`sellersku`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`domain`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`one_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`two_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`three_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`four_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`five_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`total_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`average_score`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`create_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`created_at`  timestamp NULL DEFAULT NULL ,
`updated_at`  timestamp NULL DEFAULT NULL ,
PRIMARY KEY (`asin`, `domain`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `star_history`
-- ----------------------------
DROP TABLE IF EXISTS `star_history`;
CREATE TABLE `star_history` (
`asin`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`sellersku`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`domain`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`one_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`two_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`three_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`four_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`five_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`total_star_number`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`average_score`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`create_at`  varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL ,
`created_at`  timestamp NULL DEFAULT NULL ,
`updated_at`  timestamp NULL DEFAULT NULL ,
PRIMARY KEY (`asin`, `domain`, `create_at`)
)
ENGINE=MyISAM
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `templates`
-- ----------------------------
DROP TABLE IF EXISTS `templates`;
CREATE TABLE `templates` (
`id`  int(10) NOT NULL ,
`tag`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`title`  varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL ,
`content`  text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL ,
`user_id`  int(10) NULL DEFAULT NULL ,
`created_at`  timestamp NULL DEFAULT NULL ,
`updated_at`  timestamp NULL DEFAULT NULL ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci

;

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
`id`  int(10) UNSIGNED NOT NULL ,
`name`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL ,
`email`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL ,
`password`  varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL ,
`remember_token`  varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL ,
`created_at`  timestamp NULL DEFAULT NULL ,
`updated_at`  timestamp NULL DEFAULT NULL ,
`admin`  tinyint(1) UNSIGNED ZEROFILL NOT NULL DEFAULT 0 ,
PRIMARY KEY (`id`)
)
ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci

;

-- ----------------------------
-- Indexes structure for table amazon_orders
-- ----------------------------
CREATE INDEX `SellerId,AmazonOrderId` ON `amazon_orders`(`SellerId`, `AmazonOrderId`) USING BTREE ;
CREATE INDEX `AmazonOrderId` ON `amazon_orders`(`AmazonOrderId`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table amazon_orders_item
-- ----------------------------
CREATE INDEX `orderid` ON `amazon_orders_item`(`AmazonOrderId`) USING BTREE ;
CREATE INDEX `orderid_sellerid` ON `amazon_orders_item`(`SellerId`, `AmazonOrderId`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table asin
-- ----------------------------
CREATE UNIQUE INDEX `asin,site,sellersku` ON `asin`(`asin`, `site`, `sellersku`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table asin_ads
-- ----------------------------
CREATE UNIQUE INDEX `d` ON `asin_ads`(`date_start`, `date_end`, `campaign_name`, `sku`, `site`, `seller_code`, `fee_type`, `item_code`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table asin_profits
-- ----------------------------
CREATE UNIQUE INDEX `d` ON `asin_profits`(`item_code`, `date`, `seller_code`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table asin_seller_count
-- ----------------------------
CREATE UNIQUE INDEX `site,asin` ON `asin_seller_count`(`site`, `asin`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table customers
-- ----------------------------
CREATE UNIQUE INDEX `site` ON `customers`(`site`, `customer_id`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table inbox
-- ----------------------------
CREATE UNIQUE INDEX `mail_id,mail_address` ON `inbox`(`mail_address`, `mail_id`) USING BTREE ;
CREATE INDEX `from_address` ON `inbox`(`from_address`) USING BTREE ;
CREATE INDEX `to_address` ON `inbox`(`to_address`) USING BTREE ;
CREATE INDEX `sku` ON `inbox`(`sku`) USING BTREE ;
CREATE INDEX `etype` ON `inbox`(`etype`) USING BTREE ;
CREATE INDEX `mark` ON `inbox`(`mark`) USING BTREE ;
CREATE FULLTEXT INDEX `text_html` ON `inbox`(`text_html`) ;

-- ----------------------------
-- Indexes structure for table password_resets
-- ----------------------------
CREATE INDEX `password_resets_email_index` ON `password_resets`(`email`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table review
-- ----------------------------
CREATE UNIQUE INDEX `site,review` ON `review`(`site`, `review`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table review_bak
-- ----------------------------
CREATE UNIQUE INDEX `site,review` ON `review_bak`(`site`, `review`) USING BTREE ;

-- ----------------------------
-- Indexes structure for table users
-- ----------------------------
CREATE UNIQUE INDEX `users_email_unique` ON `users`(`email`) USING BTREE ;
