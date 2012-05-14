<?php
/**
 * Mage+
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageplus.org so we can send you a copy immediately.
 *
 * @category    Mage
 * @package     Mage_Oauth
 * @copyright   Copyright (c) 2012 Mage+ (http://www.mageplus.org) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('oauth/consumer'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('oauth/token'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

$installer->getConnection()
    ->addTrigger($installer->getTable('oauth/consumer'), 'trig_' . $installer->getTable('oauth/consumer') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

$installer->getConnection()
    ->addTrigger($installer->getTable('oauth/consumer'), 'trig_' . $installer->getTable('oauth/consumer') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
$installer->getConnection()
    ->addTrigger($installer->getTable('oauth/token'), 'trig_' . $installer->getTable('oauth/token') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

$installer->endSetup();