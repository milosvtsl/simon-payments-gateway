-- MySQL dump 10.15  Distrib 10.0.29-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: localhost
-- ------------------------------------------------------
-- Server version	10.0.29-MariaDB-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `authority`
--

DROP TABLE IF EXISTS `authority`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authority` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `authority` varchar(64) DEFAULT NULL,
  `authority_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `authority`
--

LOCK TABLES `authority` WRITE;
/*!40000 ALTER TABLE `authority` DISABLE KEYS */;
INSERT INTO `authority` VALUES (1,'role_01',2,'ROLE_ADMIN','Super Admin'),(2,'role_02',11,'ROLE_POST_CHARGE','Post Charge'),(3,'role_03',9,'ROLE_VOID_CHARGE','Void Charge'),(4,'role_04',10,'ROLE_RUN_REPORTS','Run Reports'),(5,'role_05',1,'ROLE_RETURN_CHARGE','Return Charge'),(6,'role_06',12,'ROLE_SUB_ADMIN','Admin'),(7,'role_07',13,'ROLE_DEBUG','Debugger');
/*!40000 ALTER TABLE `authority` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batch`
--

DROP TABLE IF EXISTS `batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `batch_id` varchar(255) DEFAULT NULL,
  `batch_status` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `merchant_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK592D73A8775CC59` (`merchant_id`),
  CONSTRAINT `FK592D73A8775CC59` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3002 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batch`
--

LOCK TABLES `batch` WRITE;
/*!40000 ALTER TABLE `batch` DISABLE KEYS */;
/*!40000 ALTER TABLE `batch` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batch_orderitems`
--

DROP TABLE IF EXISTS `batch_orderitems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch_orderitems` (
  `id_orderitem` bigint(20) NOT NULL,
  `id_batch` bigint(20) NOT NULL,
  PRIMARY KEY (`id_batch`,`id_orderitem`),
  KEY `FK89D15757B1B250FF` (`id_orderitem`),
  KEY `FK89D15757B2CACB11` (`id_batch`),
  CONSTRAINT `FK89D15757B1B250FF` FOREIGN KEY (`id_orderitem`) REFERENCES `order_item` (`id`),
  CONSTRAINT `FK89D15757B2CACB11` FOREIGN KEY (`id_batch`) REFERENCES `batch` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batch_orderitems`
--

LOCK TABLES `batch_orderitems` WRITE;
/*!40000 ALTER TABLE `batch_orderitems` DISABLE KEYS */;
/*!40000 ALTER TABLE `batch_orderitems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_template`
--

DROP TABLE IF EXISTS `email_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(128) NOT NULL,
  `subject` varchar(256) NOT NULL,
  `body` text NOT NULL,
  `updated` datetime NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `merchant_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class` (`class`,`merchant_id`),
  KEY `merchant_id` (`merchant_id`),
  CONSTRAINT `fk_email_template_merchant_id` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_template`
--

LOCK TABLES `email_template` WRITE;
/*!40000 ALTER TABLE `email_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fee`
--

DROP TABLE IF EXISTS `fee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fee` (
  `amount` decimal(9,2) NOT NULL,
  `type` varchar(32) NOT NULL,
  `date` datetime NOT NULL,
  `order_item_id` bigint(20) NOT NULL,
  `merchant_id` bigint(20) NOT NULL,
  KEY `idx_fee_merchant_id` (`merchant_id`),
  KEY `idx_fee_order_item_id` (`order_item_id`),
  KEY `idx_fee_date` (`date`),
  CONSTRAINT `fk_fee_merchant_id` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_fee_order_item_id` FOREIGN KEY (`order_item_id`) REFERENCES `order_item` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Merchant Fees';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fee`
--

LOCK TABLES `fee` WRITE;
/*!40000 ALTER TABLE `fee` DISABLE KEYS */;
/*!40000 ALTER TABLE `fee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `integration`
--

DROP TABLE IF EXISTS `integration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `integration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `class_path` varchar(255) NOT NULL,
  `api_url_base` varchar(255) NOT NULL,
  `api_username` varchar(255) DEFAULT NULL,
  `api_password` varchar(255) DEFAULT NULL,
  `api_app_id` varchar(255) DEFAULT NULL,
  `api_type` enum('testing','production','disabled') NOT NULL DEFAULT 'testing',
  `api_credentials` text,
  `notes` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `integration_uid_unique` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integration`
--

LOCK TABLES `integration` WRITE;
/*!40000 ALTER TABLE `integration` DISABLE KEYS */;
INSERT INTO `integration` VALUES (5,'mock-mock-mock-mock','Mock Only','Integration\\Mock\\MockIntegration','http://localhost',NULL,NULL,NULL,'disabled',NULL,'Mock-Only (No integration)');
/*!40000 ALTER TABLE `integration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `integration_request`
--

DROP TABLE IF EXISTS `integration_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `integration_request` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `integration_id` int(11) NOT NULL,
  `type` enum('transaction','transaction-reversal','transaction-void','transaction-return','transaction-search','merchant','merchant-identity','merchant-provision','merchant-payment','health-check','other') NOT NULL,
  `type_id` bigint(20) NOT NULL,
  `url` varchar(145) DEFAULT NULL,
  `request` text NOT NULL,
  `response` text NOT NULL,
  `response_code` int(11) DEFAULT NULL,
  `response_message` varchar(255) DEFAULT NULL,
  `result` enum('success','fail','error') NOT NULL,
  `date` datetime NOT NULL,
  `duration` double(13,8) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `merchant_id` bigint(20) DEFAULT NULL,
  `order_item_id` bigint(20) DEFAULT NULL,
  `transaction_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_integration_request_type_type_id` (`type`,`type_id`),
  KEY `idx_integration_request_date` (`date`),
  KEY `idx_integration_request_result` (`result`),
  KEY `integration_id_fk` (`integration_id`),
  CONSTRAINT `integration_id_fk` FOREIGN KEY (`integration_id`) REFERENCES `integration` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2069 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integration_request`
--

LOCK TABLES `integration_request` WRITE;
/*!40000 ALTER TABLE `integration_request` DISABLE KEYS */;
/*!40000 ALTER TABLE `integration_request` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchant`
--

DROP TABLE IF EXISTS `merchant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `merchant` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `branch` varchar(64) NOT NULL,
  `description` varchar(128) NOT NULL,
  `address1` varchar(23) NOT NULL,
  `address2` varchar(23) DEFAULT NULL,
  `agent_chain` varchar(6) DEFAULT NULL,
  `amex_external` varchar(30) DEFAULT NULL,
  `city` varchar(64) NOT NULL,
  `convenience_fee_flat` decimal(19,2) DEFAULT NULL,
  `convenience_fee_limit` decimal(19,2) DEFAULT NULL,
  `convenience_fee_variable_rate` decimal(19,2) DEFAULT NULL,
  `discover_external` varchar(30) DEFAULT NULL,
  `main_contact` varchar(100) NOT NULL,
  `main_email_id` varchar(64) NOT NULL,
  `name` varchar(100) NOT NULL,
  `title` varchar(45) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `notes` longtext,
  `open_date` datetime DEFAULT NULL,
  `sale_rep` varchar(64) DEFAULT NULL,
  `short_name` varchar(15) DEFAULT NULL,
  `sic` varchar(4) DEFAULT NULL,
  `mcc` varchar(4) DEFAULT NULL,
  `store_id` varchar(4) DEFAULT NULL,
  `telephone` varchar(25) DEFAULT NULL,
  `zipcode` varchar(255) DEFAULT NULL,
  `country` varchar(3) DEFAULT 'USA',
  `state_id` bigint(20) DEFAULT NULL,
  `status_id` bigint(20) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `logo_path` varchar(256) DEFAULT NULL,
  `tax_id` varchar(45) DEFAULT NULL,
  `business_tax_id` varchar(45) DEFAULT NULL,
  `business_type` enum('INDIVIDUAL_SOLE_PROPRIETORSHIP','CORPORATION','LIMITED_LIABILITY_COMPANY','PARTNERSHIP','ASSOCIATION_ESTATE_TRUST','TAX_EXEMPT_ORGANIZATION','INTERNATIONAL_ORGANIZATION','GOVERNMENT_AGENCY') NOT NULL DEFAULT 'INDIVIDUAL_SOLE_PROPRIETORSHIP',
  `payout_type` enum('BANK_ACCOUNT') DEFAULT 'BANK_ACCOUNT',
  `payout_account_name` varchar(45) DEFAULT NULL,
  `payout_account_type` enum('CHECKING','SAVINGS') DEFAULT NULL,
  `payout_account_number` varchar(45) DEFAULT NULL,
  `payout_bank_code` varchar(45) DEFAULT NULL,
  `fraud_high_limit` int(11) NOT NULL DEFAULT '9999',
  `fraud_low_limit` int(11) NOT NULL DEFAULT '3',
  `fraud_high_monthly_limit` int(11) DEFAULT NULL,
  `fraud_flags` int(11) NOT NULL DEFAULT '0',
  `label_item` varchar(64) NOT NULL,
  `label_contact` varchar(64) NOT NULL,
  `integration_default_id` int(11) DEFAULT '3',
  PRIMARY KEY (`id`),
  KEY `FKE1E1C9C8155B08DB` (`state_id`),
  KEY `FKE1E1C9C8D6A4F0C1` (`status_id`),
  CONSTRAINT `FKE1E1C9C8155B08DB` FOREIGN KEY (`state_id`) REFERENCES `state` (`id`),
  CONSTRAINT `FKE1E1C9C8D6A4F0C1` FOREIGN KEY (`status_id`) REFERENCES `merchant_status` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchant`
--

LOCK TABLES `merchant` WRITE;
/*!40000 ALTER TABLE `merchant` DISABLE KEYS */;
INSERT INTO `merchant` VALUES (4,'e33359a0-e892-43ce-a3f2-df263813bae893','','','1234 Test St.',NULL,'654321','654321','Testerton',0.00,0.00,4.00,NULL,'Tony G','test@sample.com','Dr. Who',NULL,NULL,NULL,'2013-05-03 00:00:00','Tony G','Dr. Who','4900','4900','4','666 5554444','66554','USA',1,4,'http://PAYLOGICNETWORK.COM',NULL,NULL,NULL,'INDIVIDUAL_SOLE_PROPRIETORSHIP','BANK_ACCOUNT',NULL,NULL,NULL,NULL,9999,1,NULL,10,'','',3),(29,'011e1bcb-9c88-4ecc-8a08-07ba5c3e005261','','','Element Test','','0j0876','','Miami',0.00,0.00,0.00,NULL,'Element Test','test@email.com','Test Merchant (Element)','','0000-00-00','','0000-00-00 00:00:00','','Test Element',NULL,NULL,NULL,NULL,NULL,'USA',1,1,NULL,'',NULL,NULL,'INDIVIDUAL_SOLE_PROPRIETORSHIP','BANK_ACCOUNT',NULL,NULL,NULL,NULL,9999,3,NULL,0,'','',4);
/*!40000 ALTER TABLE `merchant` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchant_fee`
--

DROP TABLE IF EXISTS `merchant_fee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `merchant_fee` (
  `id` bigint(20) NOT NULL,
  `type` varchar(32) NOT NULL,
  `amount_flat` decimal(9,2) DEFAULT NULL,
  `amount_variable` decimal(9,2) DEFAULT NULL,
  `amount_limit` decimal(9,2) DEFAULT NULL,
  `entry_mode` enum('Keyed','Swipe','Check') DEFAULT NULL,
  `comment` varchar(128) DEFAULT NULL,
  `merchant_id` bigint(20) DEFAULT NULL,
  `merchant_fee_account_id` bigint(20) DEFAULT NULL,
  `integration_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_merchant_fee_merchant_id` (`merchant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Merchant Fee Schedule';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchant_fee`
--

LOCK TABLES `merchant_fee` WRITE;
/*!40000 ALTER TABLE `merchant_fee` DISABLE KEYS */;
/*!40000 ALTER TABLE `merchant_fee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchant_form`
--

DROP TABLE IF EXISTS `merchant_form`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `merchant_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `merchant_id` bigint(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `template` varchar(255) DEFAULT NULL,
  `classes` varchar(255) DEFAULT NULL,
  `fields` text,
  `flags` text,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_UNIQUE` (`uid`),
  KEY `fk_merchant_form_merchant_id_idx` (`merchant_id`)
) ENGINE=MyISAM AUTO_INCREMENT=104 DEFAULT CHARSET=latin1 COMMENT='Customized Merchant Form';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchant_form`
--

LOCK TABLES `merchant_form` WRITE;
/*!40000 ALTER TABLE `merchant_form` DISABLE KEYS */;
INSERT INTO `merchant_form` VALUES (101,'DEFAULT',NULL,'Default Charge Form','Order\\Forms\\DefaultOrderForm','','{\n    \"customer_id\": [],\n    \"invoice_number\": [],\n    \"payee_receipt_email\": [],\n    \"payee_phone_number\": [],\n    \"notes_text\": []\n}','',NULL),(99,'SIMPLE',NULL,'Simple Form','Order\\Forms\\SimpleOrderForm',NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `merchant_form` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchant_integration`
--

DROP TABLE IF EXISTS `merchant_integration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `merchant_integration` (
  `merchant_id` bigint(20) NOT NULL,
  `integration_id` int(11) NOT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `credentials` text,
  UNIQUE KEY `u_merchant_integration_id` (`merchant_id`,`integration_id`),
  KEY `fk_merchant_integration_id_idx` (`merchant_id`),
  KEY `fk_merchant_integration_integration_id_idx` (`integration_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchant_integration`
--

LOCK TABLES `merchant_integration` WRITE;
/*!40000 ALTER TABLE `merchant_integration` DISABLE KEYS */;
INSERT INTO `merchant_integration` VALUES (5,3,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1037664\",\"AccountToken\": \"D82F25FEA4656D6C37F618CE003E52D12A6FA4D290EC45E69C2F8BC1FE420DE094505801\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"024068924\", \"DefaultTerminalID\": \"S5174000101\"}'),(25,3,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1035180\",\"AccountToken\": \"9EFEB3BDEFF080D6E43D3DEBCB233915B6891B2F5731F6546BE3C146B01996D42EF8DC01\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"018852749\"}'),(24,3,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1035180\",\"AccountToken\": \"9EFEB3BDEFF080D6E43D3DEBCB233915B6891B2F5731F6546BE3C146B01996D42EF8DC01\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"018687731\"}'),(22,3,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1035180\",\"AccountToken\": \"9EFEB3BDEFF080D6E43D3DEBCB233915B6891B2F5731F6546BE3C146B01996D42EF8DC01\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"017451724\"}'),(20,3,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1035180\",\"AccountToken\": \"9EFEB3BDEFF080D6E43D3DEBCB233915B6891B2F5731F6546BE3C146B01996D42EF8DC01\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"016794792\"}'),(14,3,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1035180\",\"AccountToken\": \"9EFEB3BDEFF080D6E43D3DEBCB233915B6891B2F5731F6546BE3C146B01996D42EF8DC01\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"016752345\"}'),(6,3,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1035180\",\"AccountToken\": \"9EFEB3BDEFF080D6E43D3DEBCB233915B6891B2F5731F6546BE3C146B01996D42EF8DC01\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"015973438\"}'),(19,3,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1035180\",\"AccountToken\": \"9EFEB3BDEFF080D6E43D3DEBCB233915B6891B2F5731F6546BE3C146B01996D42EF8DC01\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"016794792\"}'),(27,4,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1021216\",\"AccountToken\": \"1EE26842EF89991F28394739F68E808196676F497FD9CF275D9235A9CF18C9F768D48B01\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"3928907\"}'),(27,3,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1037664\",\"AccountToken\": \"D82F25FEA4656D6C37F618CE003E52D12A6FA4D290EC45E69C2F8BC1FE420DE094505801\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"024068924\", \"DefaultTerminalID\": \"S5174000101\"}'),(27,6,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\n    \"MerchantProfileId\": 2095677,\n    \"propayAccountNum\": \"123456\",\n    \"propayPassword\": null\n}'),(29,4,'2017-02-11 22:59:24','2017-02-11 22:59:24','{\"AccountID\": \"1021216\",\"AccountToken\": \"1EE26842EF89991F28394739F68E808196676F497FD9CF275D9235A9CF18C9F768D48B01\" ,\"ApplicationID\": \"7731\",\"AcceptorID\": \"3928907\"}');
/*!40000 ALTER TABLE `merchant_integration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchant_status`
--

DROP TABLE IF EXISTS `merchant_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `merchant_status` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchant_status`
--

LOCK TABLES `merchant_status` WRITE;
/*!40000 ALTER TABLE `merchant_status` DISABLE KEYS */;
INSERT INTO `merchant_status` VALUES (1,'MERCH_STATUS_1',1,'Live'),(2,'MERCH_STATUS_2',1,'In Progress'),(3,'MERCH_STATUS_3',1,'Cancelled'),(4,'MERCH_STATUS_4',1,'Deleted');
/*!40000 ALTER TABLE `merchant_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `date` datetime NOT NULL,
  `description` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_field`
--

DROP TABLE IF EXISTS `order_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_field` (
  `order_id` bigint(20) NOT NULL,
  `field_name` varchar(45) NOT NULL,
  `field_value` varchar(1024) NOT NULL,
  UNIQUE KEY `unique_order_fields_id_name` (`order_id`,`field_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='custom field values for an order';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_field`
--

LOCK TABLES `order_field` WRITE;
/*!40000 ALTER TABLE `order_field` DISABLE KEYS */;
INSERT INTO `order_field` VALUES (9092,'notes_text','custom notes');
/*!40000 ALTER TABLE `order_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item`
--

DROP TABLE IF EXISTS `order_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_item` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `amount` decimal(9,2) NOT NULL,
  `card_exp_month` varchar(255) DEFAULT NULL,
  `card_exp_year` varchar(255) DEFAULT NULL,
  `card_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `entry_mode` varchar(255) NOT NULL,
  `card_type` varchar(255) DEFAULT NULL,
  `check_account_name` varchar(45) DEFAULT NULL,
  `check_account_bank_name` varchar(45) DEFAULT NULL,
  `check_account_type` enum('Checking','Savings') DEFAULT NULL,
  `check_account_number` varchar(45) DEFAULT NULL,
  `check_routing_number` varchar(45) DEFAULT NULL,
  `check_type` enum('Personal','Business') DEFAULT NULL,
  `check_number` int(11) DEFAULT NULL,
  `customer_first_name` varchar(255) DEFAULT NULL,
  `customer_id` varchar(255) DEFAULT NULL,
  `customer_last_name` varchar(255) DEFAULT NULL,
  `customermi` varchar(255) DEFAULT NULL,
  `order_item_id` varchar(255) DEFAULT NULL,
  `payee_first_name` varchar(255) DEFAULT NULL,
  `payee_last_name` varchar(255) DEFAULT NULL,
  `payee_phone_number` varchar(255) DEFAULT NULL,
  `payee_reciept_email` varchar(255) DEFAULT NULL,
  `payee_address` varchar(45) DEFAULT NULL,
  `payee_address2` varchar(45) DEFAULT NULL,
  `payee_zipcode` varchar(255) DEFAULT NULL,
  `payee_city` varchar(45) DEFAULT NULL,
  `payee_state` varchar(3) DEFAULT NULL,
  `total_returned_amount` decimal(9,2) NOT NULL DEFAULT '0.00',
  `total_returned_service_fee` decimal(9,2) DEFAULT NULL,
  `convenience_fee` decimal(5,2) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `merchant_id` bigint(20) DEFAULT NULL,
  `integration_id` int(11) DEFAULT NULL,
  `subscription_id` bigint(20) DEFAULT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `form_id` int(11) DEFAULT NULL,
  `integration_remote_id` text,
  PRIMARY KEY (`id`),
  KEY `FK2D110D648775CC59` (`merchant_id`),
  KEY `index_status` (`status`),
  KEY `index_date` (`date`),
  CONSTRAINT `FK2D110D648775CC59` FOREIGN KEY (`merchant_id`) REFERENCES `merchant` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9480 DEFAULT CHARSET=latin1 COMMENT='										';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item`
--

LOCK TABLES `order_item` WRITE;
/*!40000 ALTER TABLE `order_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payee`
--

DROP TABLE IF EXISTS `payee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payee` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `status` enum('Enabled','Disabled') DEFAULT 'Enabled',
  `payee_first_name` varchar(255) DEFAULT NULL,
  `payee_last_name` varchar(255) DEFAULT NULL,
  `payee_phone_number` varchar(255) DEFAULT NULL,
  `payee_reciept_email` varchar(255) DEFAULT NULL,
  `payee_address` varchar(45) DEFAULT NULL,
  `payee_address2` varchar(45) DEFAULT NULL,
  `payee_zipcode` varchar(255) DEFAULT NULL,
  `payee_city` varchar(45) DEFAULT NULL,
  `payee_state` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payer_uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payee`
--

LOCK TABLES `payee` WRITE;
/*!40000 ALTER TABLE `payee` DISABLE KEYS */;
/*!40000 ALTER TABLE `payee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `status` enum('Enabled','Disabled') DEFAULT 'Enabled',
  `card_number` varchar(255) DEFAULT NULL,
  `card_type` varchar(255) DEFAULT NULL,
  `card_exp_month` varchar(255) DEFAULT NULL,
  `card_exp_year` varchar(255) DEFAULT NULL,
  `check_account_number` varchar(45) DEFAULT NULL,
  `check_routing_number` varchar(45) DEFAULT NULL,
  `check_account_name` varchar(45) DEFAULT NULL,
  `check_account_bank_name` varchar(45) DEFAULT NULL,
  `check_account_type` enum('Checking','Savings') DEFAULT NULL,
  `check_type` enum('Personal','Business') DEFAULT NULL,
  `check_number` int(11) DEFAULT NULL,
  `payee_id` bigint(20) DEFAULT NULL,
  `integration_id` int(11) DEFAULT NULL,
  `integration_remote_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_uid` (`uid`),
  KEY `payment_integration_id_idx` (`integration_id`),
  KEY `payment_payer_id_idx` (`payee_id`),
  CONSTRAINT `payment_integration_id` FOREIGN KEY (`integration_id`) REFERENCES `integration` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COMMENT='										';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment`
--

LOCK TABLES `payment` WRITE;
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `state`
--

DROP TABLE IF EXISTS `state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `state` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `short_code` varchar(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_code` (`short_code`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `state`
--

LOCK TABLES `state` WRITE;
/*!40000 ALTER TABLE `state` DISABLE KEYS */;
INSERT INTO `state` VALUES (1,'STATE_01',0,'Alaska','AK'),(2,'STATE_02',0,'Alabama','AL'),(3,'STATE_03',0,'Arkansas','AR'),(4,'STATE_04',0,'Arizona','AZ'),(5,'STATE_05',0,'California','CA'),(6,'STATE_06',0,'Colorado','CO'),(7,'STATE_07',0,'Connecticut','CT'),(8,'STATE_08',0,'District of Columbia','DC'),(9,'STATE_09',0,'Delaware','DE'),(10,'STATE_10',0,'Florida','FL'),(11,'STATE_11',0,'Georgia','GA'),(12,'STATE_12',0,'Hawaii','HI'),(13,'STATE_13',0,'Iowa','IA'),(14,'STATE_14',0,'Idaho','ID'),(15,'STATE_15',0,'Illinois','IL'),(16,'STATE_16',0,'Indiana','IN'),(17,'STATE_17',0,'Kansas','KS'),(18,'STATE_18',0,'Kentucky','KY'),(19,'STATE_19',0,'Louisiana','LA'),(20,'STATE_20',0,'Massachusetts','MA'),(21,'STATE_21',0,'Maryland','MD'),(22,'STATE_22',0,'Maine','ME'),(23,'STATE_23',0,'Michigan','MI'),(24,'STATE_24',0,'Minnesota','MN'),(25,'STATE_25',0,'Missouri','MO'),(26,'STATE_26',0,'Mississippi','MS'),(27,'STATE_27',0,'Montana','MT'),(28,'STATE_28',0,'North Carolina','NC'),(29,'STATE_29',0,'North Dakota','ND'),(30,'STATE_30',0,'Nebraska','NE'),(31,'STATE_31',0,'New Hampshire','NH'),(32,'STATE_32',0,'New Jersey','NJ'),(33,'STATE_33',0,'New Mexico','NM'),(34,'STATE_34',0,'Nevada','NV'),(35,'STATE_35',0,'New York','NY'),(36,'STATE_36',0,'Ohio','OH'),(37,'STATE_37',0,'Oklahoma','OK'),(38,'STATE_38',0,'Oregon','OR'),(39,'STATE_39',0,'Pennsylvania','PA'),(40,'STATE_40',0,'Rhode Island','RI'),(41,'STATE_41',0,'South Carolina','SC'),(42,'STATE_42',0,'South Dakota','SD'),(43,'STATE_43',0,'Tennessee','TN'),(44,'STATE_44',0,'Texas','TX'),(45,'STATE_45',0,'Utah','UT'),(46,'STATE_46',0,'Virginia','VA'),(47,'STATE_47',0,'Vermont','VT'),(48,'STATE_48',0,'Washington','WA'),(49,'STATE_49',0,'Wisconsin','WI'),(50,'STATE_50',0,'West Virginia','WV'),(51,'STATE_51',0,'Wyoming','WY');
/*!40000 ALTER TABLE `state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription`
--

DROP TABLE IF EXISTS `subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint(20) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive',
  `status_message` varchar(255) DEFAULT NULL,
  `recur_amount` decimal(9,2) NOT NULL,
  `recur_count` int(11) NOT NULL,
  `recur_next_date` datetime NOT NULL,
  `recur_frequency` enum('OneTimeFuture','Daily','Weekly','BiWeekly','Monthly','BiMonthly','Quarterly','SemiAnnually','Yearly') NOT NULL,
  `recur_cancel_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_item_id_UNIQUE` (`order_item_id`),
  KEY `in_subscription_status` (`status`),
  KEY `fk_subscription_order_item_id_idx` (`order_item_id`),
  CONSTRAINT `fk_subscription_order_item_id` FOREIGN KEY (`order_item_id`) REFERENCES `order_item` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription`
--

LOCK TABLES `subscription` WRITE;
/*!40000 ALTER TABLE `subscription` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscription` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_ticket`
--

DROP TABLE IF EXISTS `support_ticket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_ticket` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(45) NOT NULL,
  `date` datetime NOT NULL,
  `status` enum('Open','Closed') DEFAULT 'Open',
  `category` varchar(45) DEFAULT 'General',
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `reply_to_email` varchar(45) DEFAULT NULL,
  `merchant_id` bigint(20) DEFAULT NULL,
  `order_item_id` bigint(20) DEFAULT NULL,
  `assigned_user_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_support_ticket_order_item_id_idx` (`order_item_id`),
  KEY `fk_support_ticket_merchant_id_idx` (`merchant_id`),
  KEY `fk_support_ticket_assigned_user_id_idx` (`assigned_user_id`),
  KEY `in_support_ticket_category` (`category`),
  KEY `in_support_ticket_status` (`status`),
  KEY `in_support_ticket_date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket`
--

LOCK TABLES `support_ticket` WRITE;
/*!40000 ALTER TABLE `support_ticket` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_ticket` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_ticket_reply`
--

DROP TABLE IF EXISTS `support_ticket_reply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_ticket_reply` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) NOT NULL,
  `date` datetime NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text,
  `from_name` varchar(45) DEFAULT NULL,
  `from_user_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_support_ticket_reply_support_ticket_id_idx` (`ticket_id`),
  KEY `fk_support_ticket_from_user_id_idx` (`from_user_id`),
  KEY `in_support_ticket_reply_date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_reply`
--

LOCK TABLES `support_ticket_reply` WRITE;
/*!40000 ALTER TABLE `support_ticket_reply` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_ticket_reply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_param`
--

DROP TABLE IF EXISTS `system_param`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_param` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `last_modified_by` varchar(255) DEFAULT NULL,
  `last_modified_time` datetime DEFAULT NULL,
  `param_category` varchar(255) DEFAULT NULL,
  `param_key` varchar(255) DEFAULT NULL,
  `param_value` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_param`
--

LOCK TABLES `system_param` WRITE;
/*!40000 ALTER TABLE `system_param` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_param` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `version` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `amount` decimal(9,2) NOT NULL,
  `auth_code_or_batch_id` varchar(255) NOT NULL,
  `capture_to` tinyint(1) NOT NULL,
  `date` datetime DEFAULT NULL,
  `entry_method` varchar(255) DEFAULT NULL,
  `is_reviewed` tinyint(1) NOT NULL,
  `return_type` varchar(255) NOT NULL,
  `returned_amount` decimal(9,2) DEFAULT NULL,
  `reviewed_by` varchar(255) DEFAULT NULL,
  `reviewed_date_time` datetime DEFAULT NULL,
  `service_fee` decimal(9,2) NOT NULL,
  `status_code` varchar(255) NOT NULL,
  `status_message` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `batch_item_id` bigint(20) DEFAULT NULL,
  `order_item_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK7FA0D2DE43F2BD98` (`order_item_id`),
  KEY `FK7FA0D2DE34D061BD` (`batch_item_id`),
  CONSTRAINT `FK7FA0D2DE34D061BD` FOREIGN KEY (`batch_item_id`) REFERENCES `batch` (`id`),
  CONSTRAINT `FK7FA0D2DE43F2BD98` FOREIGN KEY (`order_item_id`) REFERENCES `order_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction`
--

LOCK TABLES `transaction` WRITE;
/*!40000 ALTER TABLE `transaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaction` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `date` datetime DEFAULT NULL,
  `timezone` varchar(95) DEFAULT NULL,
  `authority` set('admin','sub_admin','debug','post_charge','void_charge','return_charge','run_reports') DEFAULT NULL,
  `admin_id` bigint(20) DEFAULT NULL,
  `merchant_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=latin1 COMMENT='		';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (93,'776dce8b-597e-4ce1-b273-d6e7ee27e6c8','TestMerchant@simonpayments.com','Test','Merchant','cfad9b58f4cd97acbfdf4bbbd7b86c8d','TestMerchant','2016-10-18 12:51:33','America/New_York','sub_admin',91,29);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_app`
--

DROP TABLE IF EXISTS `user_app`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_app` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `app_name` varchar(45) NOT NULL,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
  `position` tinyint(4) DEFAULT NULL,
  `cache` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `in_user_app_user_id_app_name` (`user_id`,`app_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='User App Data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_app`
--

LOCK TABLES `user_app` WRITE;
/*!40000 ALTER TABLE `user_app` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_app` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_authorities`
--

DROP TABLE IF EXISTS `user_authorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_authorities` (
  `id_user` bigint(20) NOT NULL,
  `id_authority` bigint(20) NOT NULL,
  PRIMARY KEY (`id_user`,`id_authority`),
  KEY `FKCE1004AD904394A3` (`id_authority`),
  KEY `FKCE1004ADFD943359` (`id_user`),
  CONSTRAINT `FKCE1004AD904394A3` FOREIGN KEY (`id_authority`) REFERENCES `authority` (`id`),
  CONSTRAINT `FKCE1004ADFD943359` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_authorities`
--

LOCK TABLES `user_authorities` WRITE;
/*!40000 ALTER TABLE `user_authorities` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_authorities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_merchants`
--

DROP TABLE IF EXISTS `user_merchants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_merchants` (
  `id_user` bigint(20) NOT NULL,
  `id_merchant` bigint(20) NOT NULL,
  PRIMARY KEY (`id_merchant`,`id_user`),
  KEY `FKB7BF5437CB0055D3` (`id_merchant`),
  KEY `FKB7BF5437FD943359` (`id_user`),
  CONSTRAINT `FKB7BF5437CB0055D3` FOREIGN KEY (`id_merchant`) REFERENCES `merchant` (`id`),
  CONSTRAINT `FKB7BF5437FD943359` FOREIGN KEY (`id_user`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_merchants`
--

LOCK TABLES `user_merchants` WRITE;
/*!40000 ALTER TABLE `user_merchants` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_merchants` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-03-10  0:02:41
