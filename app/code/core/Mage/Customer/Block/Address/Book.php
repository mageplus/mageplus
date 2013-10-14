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
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer address book block
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Block_Address_Book extends Mage_Core_Block_Template
{
    /**
     * @todo
     * 
     * @return
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')
            ->setTitle(Mage::helper('customer')->__('Address Book'));

        return parent::_prepareLayout();
    }

    /**
     * @todo
     * 
     * @return
     */
    public function getAddAddressUrl()
    {
        return $this->getUrl('customer/address/new', array('_secure'=>true));
    }

    /**
     * @todo
     * 
     * @return
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/', array('_secure'=>true));
    }

    /**
     * @todo
     * 
     * @return
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('customer/address/delete');
    }

    /**
     * @todo
     *
     * @param $address
     * 
     * @return
     */
    public function getAddressEditUrl($address)
    {
        return $this->getUrl('customer/address/edit', array('_secure'=>true, 'id'=>$address->getId()));
    }

    /**
     * @todo
     * 
     * @return
     */
    public function getPrimaryBillingAddress()
    {
        return $this->getCustomer()->getPrimaryBillingAddress();
    }

    /**
     * @todo
     * 
     * @return
     */
    public function getPrimaryShippingAddress()
    {
        return $this->getCustomer()->getPrimaryShippingAddress();
    }

    /**
     * @todo
     * 
     * @return
     */
    public function hasPrimaryAddress()
    {
        return $this->getPrimaryBillingAddress() || $this->getPrimaryShippingAddress();
    }

    /**
     * @todo
     * 
     * @return
     */
    public function getAdditionalAddresses()
    {
        $addresses = $this->getCustomer()->getAdditionalAddresses();
        return empty($addresses) ? false : $addresses;
    }

    /**
     * @todo
     *
     * @param $address
     * 
     * @return
     */
    public function getAddressHtml($address)
    {
        return $address->format('html');
        //return $address->toString($address->getHtmlFormat());
    }

    /**
     * @todo
     * 
     * @return
     */
    public function getCustomer()
    {
        $customer = $this->getData('customer');
        if (is_null($customer)) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            $this->setData('customer', $customer);
        }
        return $customer;
    }
}
