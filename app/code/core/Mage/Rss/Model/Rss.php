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
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Auth session model
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Model_Rss
{
    protected $_feedArray = array();

   /**
    * @todo
    *
    * @param array $data
    * @return
    */
    public function _addHeader($data = array())
    {
        $this->_feedArray = $data;
        return $this;
    }

   /**
    * @todo
    *
    * @param $entries
    * @return
    */
    public function _addEntries($entries)
    {
        $this->_feedArray['entries'] = $entries;
        return $this;
    }

   /**
    * @todo
    *
    * @param $entry
    * @return
    */
    public function _addEntry($entry)
    {
        $this->_feedArray['entries'][] = $entry;
        return $this;
    }

   /**
    * @todo
    *
    * @return
    */
    public function getFeedArray()
    {
        return $this->_feedArray;
    }

   /**
    * @todo
    *
    * @return
    */
    public function createRssXml()
    {
        try {
            $rssFeedFromArray = Zend_Feed::importArray($this->getFeedArray(), 'rss');
            return $rssFeedFromArray->saveXML();
        } catch (Exception $e) {
            return Mage::helper('rss')->__('Error in processing xml. %s',$e->getMessage());
        }
    }
}
