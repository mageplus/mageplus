<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer account form block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_View
 extends Mage_Adminhtml_Block_Template
 implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_customer;

    protected $_customerLog;

    /**
     * @todo
     *
     * @return
     */
    public function getCustomer()
    {
        if (!$this->_customer) {
            $this->_customer = Mage::registry('current_customer');
        }
        return $this->_customer;
    }

    /**
     * @todo
     *
     * @return
     */
    public function getGroupName()
    {
        if ($groupId = $this->getCustomer()->getGroupId()) {
            return Mage::getModel('customer/group')
                ->load($groupId)
                ->getCustomerGroupCode();
        }
    }

    /**
     * Load Customer Log model
     *
     * @return Mage_Log_Model_Customer
     */
    public function getCustomerLog()
    {
        if (!$this->_customerLog) {
            $this->_customerLog = Mage::getModel('log/customer')
                ->loadByCustomer($this->getCustomer()->getId());
        }
        return $this->_customerLog;
    }

    /**
     * Get customer creation date
     *
     * @return string
     */
    public function getCreateDate()
    {
        return Mage::helper('core')->formatDate($this->getCustomer()->getCreatedAtTimestamp(),
            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
    }

    /**
     * @todo
     *
     * @return
     */
    public function getStoreCreateDate()
    {
        $date = Mage::app()->getLocale()->storeDate(
            $this->getCustomer()->getStoreId(),
            $this->getCustomer()->getCreatedAtTimestamp(),
            true
        );
        return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
    }

    /**
     * @todo
     *
     * @return
     */
    public function getStoreCreateDateTimezone()
    {
        return Mage::app()->getStore($this->getCustomer()->getStoreId())
            ->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
    }

    /**
     * Get customer last login date
     *
     * @return string
     */
    public function getLastLoginDate()
    {
        $date = $this->getCustomerLog()->getLoginAtTimestamp();
        if ($date) {
            return Mage::helper('core')->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
        }
        return Mage::helper('customer')->__('Never');
    }

    /**
     * @todo
     *
     * @return
     */
    public function getStoreLastLoginDate()
    {
        if ($date = $this->getCustomerLog()->getLoginAtTimestamp()) {
            $date = Mage::app()->getLocale()->storeDate(
                $this->getCustomer()->getStoreId(),
                $date,
                true
            );
            return $this->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM, true);
        }
        return Mage::helper('customer')->__('Never');
    }

    /**
     * @todo
     *
     * @return
     */
    public function getStoreLastLoginDateTimezone()
    {
        return Mage::app()->getStore($this->getCustomer()->getStoreId())
            ->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE);
    }

    /**
     * @todo
     *
     * @return
     */
    public function getCurrentStatus()
    {
        $log = $this->getCustomerLog();
        if ($log->getLogoutAt() ||
            strtotime(now())-strtotime($log->getLastVisitAt())>Mage_Log_Model_Visitor::getOnlineMinutesInterval()*60) {
            return Mage::helper('customer')->__('Offline');
        }
        return Mage::helper('customer')->__('Online');
    }

    /**
     * @todo
     *
     * @return
     */
    public function getIsConfirmedStatus()
    {
        $this->getCustomer();
        if (!$this->_customer->getConfirmation()) {
            return Mage::helper('customer')->__('Confirmed');
        }
        if ($this->_customer->isConfirmationRequired()) {
            return Mage::helper('customer')->__('Not confirmed, cannot login');
        }
        return Mage::helper('customer')->__('Not confirmed, can login');
    }

    /**
     * @todo
     *
     * @return
     */
    public function getCreatedInStore()
    {
        return Mage::app()->getStore($this->getCustomer()->getStoreId())->getName();
    }

    /**
     * @todo
     *
     * @return
     */
    public function getStoreId()
    {
        return $this->getCustomer()->getStoreId();
    }

    /**
     * @todo
     *
     * @return
     */
    public function getBillingAddressHtml()
    {
        $html = '';
        if ($address = $this->getCustomer()->getPrimaryBillingAddress()) {
            $html = $address->format('html');
        }
        else {
            $html = Mage::helper('customer')->__('The customer does not have default billing address.');
        }
        return $html;
    }

    /**
     * @todo
     *
     * @return
     */
    public function getAccordionHtml()
    {
        return $this->getChildHtml('accordion');
    }

    /**
     * @todo
     *
     * @return
     */
    public function getSalesHtml()
    {
        return $this->getChildHtml('sales');
    }

    /**
     * @todo
     *
     * @return
     */
    public function getTabLabel()
    {
        return Mage::helper('customer')->__('Customer View');
    }

    /**
     * @todo
     *
     * @return
     */
    public function getTabTitle()
    {
        return Mage::helper('customer')->__('Customer View');
    }

    /**
     * @todo
     *
     * @return
     */
    public function canShowTab()
    {
        if (Mage::registry('current_customer')->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @todo
     *
     * @return
     */
    public function isHidden()
    {
        if (Mage::registry('current_customer')->getId()) {
            return false;
        }
        return true;
    }

}
