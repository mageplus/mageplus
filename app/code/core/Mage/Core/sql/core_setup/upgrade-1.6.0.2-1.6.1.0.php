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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Mage+ (http://www.mageplus.org) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// redefine last_update column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('core/flag'), 'last_update', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Date of last flag update'
), true);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('core/flag'), 'trig_' . $installer->getTable('core/flag') . '_updated',
                  'FOR EACH ROW SET NEW.last_update = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);

// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('core/cache'), 'trig_' . $installer->getTable('core/cache') . '_created',
                  'FOR EACH ROW SET NEW.create_time = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('core/cache'), 'trig_' . $installer->getTable('core/cache') . '_updated',
                  'FOR EACH ROW SET NEW.update_time = UTC_TIMESTAMP, NEW.create_time = OLD.create_time',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('core/email_template'), 'trig_' . $installer->getTable('core/email_template') . '_created',
                  'FOR EACH ROW SET NEW.added_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('core/email_template'), 'trig_' . $installer->getTable('core/email_template') . '_updated',
                  'FOR EACH ROW SET NEW.modified_at = UTC_TIMESTAMP, NEW.added_at = OLD.added_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);

$installer->endSetup();