<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-ENTERPRISE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento ENTERPRISE edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento ENTERPRISE edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

	$installer->run("
		SET FOREIGN_KEY_CHECKS=0;
		
		ALTER TABLE `galleryweek_comment_notification` DROP KEY `post_id`;
		
		ALTER TABLE `galleryweek` ENGINE = MyISAM;
		
		ALTER TABLE `galleryweek` ADD FULLTEXT (`heading`);
		
		ALTER TABLE `galleryweek` ADD FULLTEXT (`description`);
		
		ALTER TABLE `galleryweek` ADD FULLTEXT (`tags`);

		SET FOREIGN_KEY_CHECKS=1;
	");

$installer->endSetup();

