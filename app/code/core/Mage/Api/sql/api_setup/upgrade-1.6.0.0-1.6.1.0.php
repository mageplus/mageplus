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
 * @package     Mage_Api
 * @copyright   Copyright (c) 2012 Mage+ (http://www.mageplus.org)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// redefine created column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('api/user'), 'created', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'User record create date'
), true);

// redefine modified column, so that default is 0000-00-00 00:00:00
$installer->getConnection()->modifyColumn($installer->getTable('api/user'), 'modified', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'User record modify date'
), true);

// redefine logdate column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('api/session'), 'logdate', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Login date'
), true);

// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('api/user'), 'trig_' . $installer->getTable('api/user') . '_created',
                  'FOR EACH ROW SET NEW.created = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('api/user'), 'trig_' . $installer->getTable('api/user') . '_updated',
                  'FOR EACH ROW SET NEW.modified = UTC_TIMESTAMP, NEW.created = OLD.created',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('api/session'), 'trig_' . $installer->getTable('api/session') . '_updated',
                  'FOR EACH ROW SET NEW.logdate = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);

$installer->endSetup();
