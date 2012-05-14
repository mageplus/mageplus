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
 * @package     Mage_Log
 * @copyright   Copyright (c) 2012 Mage+ (http://www.mageplus.org) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// redefine login_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('log/customer'), 'login_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Login Time'
), true);

// redefine create_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('log/quote_table'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Creation Time'
), true);

// redefine add_date column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('log/summary_table'), 'add_date', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Date'
), true);

// redefine visit_time column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('log/url_table'), 'visit_time', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Visit Time'
), true);

$installer->getConnection()
    ->addTrigger($installer->getTable('log/customer'), 'trig_' . $installer->getTable('log/customer') . '_created',
                  'FOR EACH ROW SET NEW.login_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

$installer->getConnection()
    ->addTrigger($installer->getTable('log/quote_table'), 'trig_' . $installer->getTable('log/quote_table') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
$installer->getConnection()
    ->addTrigger($installer->getTable('log/summary_table'), 'trig_' . $installer->getTable('log/summary_table') . '_created',
                  'FOR EACH ROW SET NEW.add_date = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
$installer->getConnection()
    ->addTrigger($installer->getTable('log/url_table'), 'trig_' . $installer->getTable('log/url_table') . '_created',
                  'FOR EACH ROW SET NEW.visit_time = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
$installer->getConnection()
    ->addTrigger($installer->getTable('log/visitor'), 'trig_' . $installer->getTable('log/visitor') . '_created',
                  'FOR EACH ROW SET NEW.first_visit_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
$installer->getConnection()
    ->addTrigger($installer->getTable('log/visitor'), 'trig_' . $installer->getTable('log/visitor') . '_updated',
                  'FOR EACH ROW SET NEW.last_visit_at = UTC_TIMESTAMP, NEW.first_visit_at = OLD.first_visit_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
$installer->getConnection()
    ->addTrigger($installer->getTable('log/visitor_online'), 'trig_' . $installer->getTable('log/visitor_online') . '_created',
                  'FOR EACH ROW SET NEW.first_visit_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
$installer->getConnection()
    ->addTrigger($installer->getTable('log/visitor_online'), 'trig_' . $installer->getTable('log/visitor_online') . '_updated',
                  'FOR EACH ROW SET NEW.last_visit_at = UTC_TIMESTAMP, NEW.first_visit_at = OLD.first_visit_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);

$installer->endSetup();