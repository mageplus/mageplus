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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Mage+ (http://www.mageplus.org) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('sales/billing_agreement'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('sales/quote'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('sales/quote_item'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('sales/quote_address'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('sales/order_item'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('sales/quote_payment'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('sales/recurring_profile'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('sales/quote_address_item'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// redefine created_at column, so that default is 0000-00-00 00:00:00 instead of CURRENT_TIMESTAMP
$installer->getConnection()->modifyColumn($installer->getTable('sales/quote_address_shipping_rate'), 'created_at', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
    'nullable' => true,
    'default' => '0000-00-00 00:00:00',
    'comment' => 'Created At'
), true);

// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/payment_transaction'), 'trig_' . $installer->getTable('sales/payment_transaction') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/recurring_profile'), 'trig_' . $installer->getTable('sales/recurring_profile') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/recurring_profile'), 'trig_' . $installer->getTable('sales/recurring_profile') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);

// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/order_item'), 'trig_' . $installer->getTable('sales/order_item') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/order_item'), 'trig_' . $installer->getTable('sales/order_item') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote'), 'trig_' . $installer->getTable('sales/quote') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote'), 'trig_' . $installer->getTable('sales/quote') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_address'), 'trig_' . $installer->getTable('sales/quote_address') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_address'), 'trig_' . $installer->getTable('sales/quote_address') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_address_item'), 'trig_' . $installer->getTable('sales/quote_address_item') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_address_item'), 'trig_' . $installer->getTable('sales/quote_address_item') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_item'), 'trig_' . $installer->getTable('sales/quote_item') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_item'), 'trig_' . $installer->getTable('sales/quote_item') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_payment'), 'trig_' . $installer->getTable('sales/quote_payment') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_payment'), 'trig_' . $installer->getTable('sales/quote_payment') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_address_shipping_rate'), 'trig_' . $installer->getTable('sales/quote_address_shipping_rate') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/quote_address_shipping_rate'), 'trig_' . $installer->getTable('sales/quote_address_shipping_rate') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);

// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/creditmemo'), 'trig_' . $installer->getTable('sales/creditmemo') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/creditmemo'), 'trig_' . $installer->getTable('sales/creditmemo') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/creditmemo_comment'), 'trig_' . $installer->getTable('sales/creditmemo_comment') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/creditmemo_grid'), 'trig_' . $installer->getTable('sales/creditmemo_grid') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/order'), 'trig_' . $installer->getTable('sales/order') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/order'), 'trig_' . $installer->getTable('sales/order') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/order_grid'), 'trig_' . $installer->getTable('sales/order_grid') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/order_grid'), 'trig_' . $installer->getTable('sales/order_grid') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/order_status_history'), 'trig_' . $installer->getTable('sales/order_status_history') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/invoice'), 'trig_' . $installer->getTable('sales/invoice') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/invoice'), 'trig_' . $installer->getTable('sales/invoice') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/invoice_grid'), 'trig_' . $installer->getTable('sales/invoice_grid') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/invoice_comment'), 'trig_' . $installer->getTable('sales/invoice_comment') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/billing_agreement'), 'trig_' . $installer->getTable('sales/billing_agreement') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/billing_agreement'), 'trig_' . $installer->getTable('sales/billing_agreement') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/shipment'), 'trig_' . $installer->getTable('sales/shipment') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/shipment'), 'trig_' . $installer->getTable('sales/shipment') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);
    
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/shipment_grid'), 'trig_' . $installer->getTable('sales/shipment_grid') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/shipment_comment'), 'trig_' . $installer->getTable('sales/shipment_comment') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);
    
// add trigger on create
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/shipment_track'), 'trig_' . $installer->getTable('sales/shipment_track') . '_created',
                  'FOR EACH ROW SET NEW.created_at = UTC_TIMESTAMP',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_INSERT);

// add trigger on update
$installer->getConnection()
    ->addTrigger($installer->getTable('sales/shipment_track'), 'trig_' . $installer->getTable('sales/shipment_track') . '_updated',
                  'FOR EACH ROW SET NEW.updated_at = UTC_TIMESTAMP, NEW.created_at = OLD.created_at',
                  Varien_Db_Adapter_Interface::TRIGGER_TIME_BEFORE, Varien_Db_Adapter_Interface::EVENT_TYPE_UPDATE);

$installer->endSetup();
