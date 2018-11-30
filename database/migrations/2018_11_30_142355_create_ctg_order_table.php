<?php

use Illuminate\Database\Migrations\Migration;

class CreateCtgOrderTable extends Migration {
    use \App\Traits\Migration;

    public function up() {
        $this->statement(
            "
            CREATE TABLE IF NOT EXISTS `ctg_order` (
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
              `created_at` timestamp NOT NULL,
              `updated_at` timestamp NOT NULL,
              KEY `AmazonOrderId` (`AmazonOrderId`),
              KEY `SellerId` (`SellerId`),
              KEY `MarketPlaceId` (`MarketPlaceId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            

            "
        );
        $this->statement(
            "
            CREATE TABLE IF NOT EXISTS `ctg_order_item` (
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
              `created_at` timestamp NOT NULL,
              `updated_at` timestamp NOT NULL,
              KEY `SellerId` (`SellerId`),
              KEY `MarketPlaceId` (`MarketPlaceId`),
              KEY `AmazonOrderId` (`AmazonOrderId`),
              KEY `ASIN` (`ASIN`),
              KEY `SellerSKU` (`SellerSKU`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            

            "
        );
    }

    /**
     * @throws Exception
     */
    public function down() {
        $this->dropTableIfEmpty('ctg_order');
        $this->dropTableIfEmpty('ctg_order_item');
    }
}
