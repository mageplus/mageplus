<?php
/**
 * PHP Unit test suite for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Mage
 * @package    Mage_Test
 * @copyright  Copyright (c) 2012 EcomDev BV (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan.chepurnyi@ecomdev.org>
 */

/**
 * Expectation data object used for retrieving of data from expectations array
 * Created to make possible iteration of expected data,
 * for instance if you have list of orders
 *
 */
class Mage_Test_Model_Expectation_Object
    extends Varien_Object
    implements Iterator
{
    protected $_createdObjectIds = array();

    /**
     * Current key in iterator
     *
     * @var array
     */
    protected $_iterationKeys = array();

    /**
     * If current element is an array,
     * then it will be automatically wrapped by
     * the same class instance as this one
     *
     * @see Iterator::current()
     * @return null|int|string|boolean|decimal|Mage_Test_Model_Expectation_Object
     */
    public function current()
    {
        if ($this->key() === null) {
            return null;
        }

        $current = $this->_data[$this->key()];
        if (is_array($current)) {
            $newObject = new self($current);
            $this->_createdObjectIds = Mage::objects()
                ->save($current);
            return $newObject;
        }
        return $current;
    }

		/* (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key()
    {
        return current($this->_iterationKeys);
    }

		/* (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next()
    {
        next($this->_iterationKeys);
    }

		/* (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->_iterationKeys = $this->keys();
    }

		/* (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid()
    {
        return key($this->_iterationKeys) !== null;
    }

    /**
     * Object destructor removes
     * created objects from object pool
     *
     *
     */
    public function __destruct()
    {
        if (!empty($this->_createdObjectIds)) {
            foreach ($this->_createdObjectIds as $objectId) {
                Mage::objects()->delete($objectId);
            }
        }
    }

    /**
     * Returns data array keys
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->_data);
    }

    /**
     * Returns data array values
     *
     * @return array
     */
    public function values()
    {
        return array_values($this->_data);
    }
}