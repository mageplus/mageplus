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
 * @package    Mage_PHPUnit
 * @copyright  Copyright (c) 2012 EcomDev BV (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan.chepurnyi@ecomdev.org>
 */

/**
 * Abstract class for constraints based on configuration
 *
 */
abstract class Mage_PHPUnit_Constraint_Config_Abstract
    extends Mage_PHPUnit_Constraint_Abstract
    implements Mage_PHPUnit_Constraint_Config_Interface
{
    /**
     * Config node path defined in the constructor
     *
     * @var string
     */
    protected $_nodePath = null;

    /**
     * Constraint constructor
     *
     * @param string $nodePath
     * @param string $type
     * @param mixed $expectedValue
     */
    public function __construct($nodePath, $type, $expectedValue = null)
    {
        if (empty($nodePath) || !is_string($nodePath)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string', $type);
        }

        $this->_nodePath = $nodePath;
        parent::__construct($type, $expectedValue);
    }

    /**
     * Returns node path for checking
     *
     * (non-PHPdoc)
     * @see Mage_PHPUnit_Constraint_Config_Interface::getNodePath()
     */
    public function getNodePath()
    {
        return $this->_nodePath;
    }

    /**
     * Automatically evaluate to false if the node was not found
     *
     * (non-PHPdoc)
     * @see Mage_PHPUnit_Constraint_Abstract::evaluate()
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        if ($other === false) {
            // If node was not found, than evaluation fails
            return false;
        }

        return parent::evaluate($other, $description, $returnResult);
    }

    /**
     * Returns a scalar representation of actual value,
     * Returns $other if internal acutal value is not set
     *
     * @param Varien_Simplexml_Element $other
     * @return scalar
     */
    protected function getActualValue($other = null)
    {
        if (!$this->_useActualValue && $other->hasChildren()) {
            return $other->asNiceXml();
        } elseif (!$this->_useActualValue) {
            return (string) $other;
        }

        return parent::getActualValue($other);
    }

    /**
     * Returns a scalar representation of expected value
     *
     * @return string
     */
    protected function getExpectedValue()
    {
        if ($this->_expectedValue instanceof Varien_Simplexml_Element) {
            return $this->_expectedValue->asNiceXml();
        }

        return parent::getExpectedValue();
    }
}