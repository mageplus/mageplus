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
class Mage_Adminhtml_Model_Report_Item extends Varien_Object
{
    protected $_isEmpty  = false;
    protected $_children = array();

    /**
     * @todo
     *
     * @param bool $flag
     * @return
     */
    public function setIsEmpty($flag = true)
    {
        $this->_isEmpty = $flag;
        return $this;
    }

    /**
     * @todo
     *
     * @return
     */
    public function getIsEmpty()
    {
        return $this->_isEmpty;
    }

    /**
     * @todo
     *
     * @return
     */
    public function hasIsEmpty()
    {}

    /**
     * @todo
     *
     * @return
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * @todo
     *
     * @param $children
     * @return
     */
    public function setChildren($children)
    {
        $this->_children = $children;
        return $this;
    }

    /**
     * @todo
     *
     * @return
     */
    public function hasChildren()
    {
        return (count($this->_children) > 0) ? true : false;
    }

    /**
     * @todo
     *
     * @param $child
     * @return
     */
    public function addChild($child)
    {
        $this->_children[] = $child;
        return $this;
    }
}
