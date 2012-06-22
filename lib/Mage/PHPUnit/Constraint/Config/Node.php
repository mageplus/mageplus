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
 * Configuration node constaint
 *
 */
class Mage_PHPUnit_Constraint_Config_Node
    extends Mage_PHPUnit_Constraint_Config_Abstract
{
    const TYPE_EQUALS_STRING = 'equals_string';
    const TYPE_EQUALS_BOOLEAN = 'equals_boolean';
    const TYPE_EQUALS_XML = 'equals_xml';
    const TYPE_EQUALS_NUMBER = 'equals_decimal';
    const TYPE_LESS_THAN = 'less_than';
    const TYPE_GREATER_THAN = 'greater_than';
    const TYPE_CONTAIN_VALUE  = 'contain_values';
    const TYPE_HAS_CHILD  = 'has_child';
    const TYPE_HAS_CHILDREN = 'has_children';

    /**
     * Contraint for evaluation of config node
     *
     * @param string $moduleName
     * @param string $type
     * @param mixed $expectedValue
     */
    public function __construct($nodePath, $type, $expectedValue = null)
    {
        $this->_expectedValueValidation += array(
            self::TYPE_EQUALS_STRING => array(true, 'is_string', 'string'),
            self::TYPE_EQUALS_NUMBER => array(true, 'is_numeric', 'numeric'),
            self::TYPE_LESS_THAN => array(true, 'is_numeric', 'numeric'),
            self::TYPE_GREATER_THAN => array(true, 'is_numeric', 'numeric'),
            self::TYPE_CONTAIN_VALUE => array(true, 'is_scalar', 'scalar'),
            self::TYPE_HAS_CHILD => array(true, 'is_string', 'string')
        );

        $this->_typesWithDiff[] = self::TYPE_EQUALS_STRING;
        $this->_typesWithDiff[] = self::TYPE_EQUALS_BOOLEAN;
        $this->_typesWithDiff[] = self::TYPE_EQUALS_XML;
        $this->_typesWithDiff[] = self::TYPE_EQUALS_NUMBER;
        $this->_typesWithDiff[] = self::TYPE_LESS_THAN;
        $this->_typesWithDiff[] = self::TYPE_GREATER_THAN;

        parent::__construct($nodePath, $type, $expectedValue);
    }

    /**
     * Check that node value is equal to string
     *
     * @param Varien_Simplexml_Element $other
     */
    protected function evaluateEqualsString($other)
    {
        return (string)$other === $this->_expectedValue;
    }

    /**
     * Text representation of the assertion
     *
     * @return string
     */
    protected function textEqualsString()
    {
        return 'is equal to expected string';
    }

    /**
     * Check that node value is equal to number
     *
     * @param Varien_Simplexml_Element $other
     */
    protected function evaluateEqualsNumber($other)
    {
        return (float)$other == $this->_expectedValue;
    }

    /**
     * Text representation of the assertion
     *
     * @return string
     */
    protected function textEqualsNumber()
    {
        return 'is equal to expected number';
    }

    /**
     * Check that node value is a string with such properties
     *
     * @param Varien_Simplexml_Element $other
     */
    protected function evaluateLessThan($other)
    {
        return (float)$other > (float)$this->_expectedValue;
    }

    /**
     * Text representation of the assertion
     *
     * @return string
     */
    protected function textLessThan()
    {
        return sprintf('is less than %s', (float)$this->_expectedValue);
    }

    /**
     * Check that node value is a string with such properties
     *
     * @param Varien_Simplexml_Element $other
     */
    protected function evaluateGreaterThan($other)
    {
        return (float)$other > (float)$this->_expectedValue;
    }

    /**
     * Text representation of the assertion
     *
     * @return string
     */
    protected function textGreaterThan()
    {
        return sprintf('is greater than %s', (float)$this->_expectedValue);
    }

    /**
     * Checks that string is not false value of a config flag
     *
     * @param Varien_Simplexml_Element $other
     */
    protected function evaluateEqualsBoolean($other)
    {
        $other = (string) $other;
        return !empty($other) && $other !== 'false';
    }

    /**
     * Returns text reperesentation of flag checking
     *
     * @return string
     */
    protected function textEqualsBoolean()
    {
        return 'is equals to boolean flag true';
    }

    /**
     * Checks expected xml value with current configuration
     *
     * @param Varien_Simplexml_Element $other
     * @throws RuntimeException if expected value is a valid xml object
     */
    protected function evaluateEqualsXml($other)
    {
        // Normalize expected XML for matching appropriate xml structure without
        // whitespaces, etc
        if (!$this->_expectedValue instanceof Varien_Simplexml_Element) {
            try {
               if ($other instanceof SimpleXMLElement) {
                   $other = $other->asXML();
               }
               $expectedXml = new Varien_Simplexml_Element($this->_expectedValue);
            } catch (Exception $e) {
                throw new RuntimeException(sprintf(
                    'Expected value is not an xml string for node %s, passed expected value: %s, parsing error: %s',
                    $this->_nodePath,
                    PHPUnit_Util_Type::toString($this->_expectedValue),
                    $e->getMessage()
                ));
            }

            $this->_expectedValue = $expectedXml;
        }

        return $this->_expectedValue->asNiceXml() == $other->asNiceXml();
    }

    /**
     * Returns text representatation of xml comparisment
     *
     * @return string
     */
    protected function textEqualsXml()
    {
        return 'is the same as expected XML value';
    }

    /**
     * Checks existance of a child with the expected value as the name
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateHasChild($other)
    {
        if (!$other->hasChildren()) {
            return false;
        }

        if (!isset($other->{$this->_expectedValue})) {
           return false;
        }

        return true;
    }

    /**
     * Returns text represetnation of contain child assert
     *
     * @return string
     */
    protected function textHasChild()
    {
        return sprintf('has "%s" child node', $this->_expectedValue);
    }

    /**
     * Checks that configuration node has children
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateHasChildren($other)
    {
        return $other->hasChildren();
    }

    /**
     * Returns text representation of has children assert
     *
     * @return string
     */
    protected function textHasChildren()
    {
        return 'has children';
    }

    /**
     * Checks multiple values nodes.
     * Values are comma separated string
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateContainValue($other)
    {
        if ($other->hasChildren()) {
            throw new RuntimeException(sprintf(
                'Config node "%s" is not a string of comma separated values, passed expected value: %s',
                $this->_nodePath,
                PHPUnit_Util_Type::toString($this->_expectedValue)
            ));
        }

        $values = explode(',', (string)$other);

        if (!in_array($this->_expectedValue, $values)) {
            return true;
        }

        return false;
    }

    /**
     * Returns text representation of contain value assert
     *
     * @return string
     */
    protected function textContainValue()
    {
        return sprintf('contains "%s" in comma separated value list',
                       PHPUnit_Util_Type::toString($this->_expectedValue));
    }

    /**
     * Custom failure description for showing config related errors
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
          'Failed asserting that configuration node "%s" %s.', $this->_nodePath, $this->toString()
        );
    }
}
