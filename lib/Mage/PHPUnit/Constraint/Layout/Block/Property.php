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
 * @category   EcomDev
 * @package    EcomDev_PHPUnit
 * @copyright  Copyright (c) 2012 EcomDev BV (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan.chepurnyi@ecomdev.org>
 */

/**
 * Block property constraint
 *
 */
class EcomDev_PHPUnit_Constraint_Layout_Block_Property
    extends EcomDev_PHPUnit_Constraint_Layout_Abstract
{
    const TYPE_CONSTRAINT = 'constraint';

    /**
     * Block name for constraint
     *
     * @var string
     */
    protected $_blockName = null;

    /**
     * Block property for constraint
     *
     * @var string
     */
    protected $_propertyName = null;

    /**
     * Block property constraint
     *
     * @return boolean
     */
    public function __construct($blockName, $propertyName, PHPUnit_Framework_Constraint $constraint,
        $type = self::TYPE_CONSTRAINT)
    {
        if (empty($blockName) || !is_string($blockName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string', $blockName);
        }

        if (empty($propertyName) || !is_string($propertyName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string', $propertyName);
        }

        parent::__construct($type, $constraint);

        $this->_blockName = $blockName;
        $this->_propertyName = $propertyName;
    }

    /**
     * Retuns number of constraint assertions
     *
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::count()
     */
    public function count()
    {
        return $this->_expectedValue->count();
    }

    /**
     * Returning user friendly actual value
     * (non-PHPdoc)
     * @see EcomDev_PHPUnit_Constraint_Abstract::getActualValue()
     */
    protected function getActualValue($other)
    {
        if ($this->_useActualValue) {
            if ($this->_actualValue instanceof Varien_Object) {
                $value = $this->_actualValue->debug();
            } else {
                $value = $this->_actualValue;
            }

            return PHPUnit_Util_Type::toString($value);
        }

        return '';
    }

    /**
     * Evaluates a property constraint
     *
     * @param EcomDev_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return boolean
     */
    protected function evaluateConstraint($other)
    {
        $this->setActualValue(
            $other->getBlockProperty($this->_blockName, $this->_propertyName)
        );

        return $this->_expectedValue->evaluate($this->_actualValue);
    }

    /**
     * Text representation of block property constraint
     *
     * @return string
     */
    protected function textConstraint()
    {
        return sprintf('block "%s" property "%s" %s',
                      $this->_blockName, $this->_propertyName,
                      $this->_expectedValue->toString());
    }
}
