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
 * Module configuration constraint
 *
 */
class Mage_PHPUnit_Constraint_Config_Module
    extends Mage_PHPUnit_Constraint_Config_Abstract
{
    const XML_PATH_MODULE_NODE = 'modules/%s';

    const TYPE_IS_ACTIVE = 'is_active';
    const TYPE_CODE_POOL = 'code_pool';
    const TYPE_DEPENDS = 'depends';
    const TYPE_EQUALS_VERSION = 'version';
    const TYPE_LESS_THAN_VERSION = 'version_less_than';
    const TYPE_GREATER_THAN_VERSION = 'version_greater_than';

    /**
     * Name of the module for constraint
     *
     * @var string
     */
    protected $_moduleName = null;

    /**
     * Contraint for evaluation of module config node
     *
     * @param string $nodePath
     * @param string $type
     * @param mixed $expectedValue
     */
    public function __construct($moduleName, $type, $expectedValue)
    {
        $this->_expectedValueValidation += array(
            self::TYPE_CODE_POOL => array(true, 'is_string', 'string'),
            self::TYPE_DEPENDS => array(true, 'is_string', 'string'),
            self::TYPE_EQUALS_VERSION => array(true, 'is_string', 'string'),
            self::TYPE_LESS_THAN_VERSION => array(true, 'is_string', 'string'),
            self::TYPE_GREATER_THAN_VERSION => array(true, 'is_string', 'string'),
        );

        $this->_typesWithDiff[] = self::TYPE_CODE_POOL;
        $this->_typesWithDiff[] = self::TYPE_EQUALS_VERSION;
        $this->_typesWithDiff[] = self::TYPE_LESS_THAN_VERSION;
        $this->_typesWithDiff[] = self::TYPE_GREATER_THAN_VERSION;

        parent::__construct(
            sprintf(self::XML_PATH_MODULE_NODE, $moduleName),
            $type,
            $expectedValue
        );

        $this->_moduleName = $moduleName;
    }

    /**
     * Evaluates module is active
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateIsActive($other)
    {
        return $other->is('active');
    }

    /**
     * Text representation of module is active constraint
     *
     * @return string
     */
    protected function textIsActive()
    {
        return 'is active';
    }

    /**
     * Evaluates module code pool
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateCodePool($other)
    {
        $this->setActualValue((string)$other->codePool);

        return  $this->_actualValue === $this->_expectedValue;
    }

    /**
     * Text representation of module is active constraint
     *
     * @return string
     */
    protected function textCodePool()
    {
        return sprintf('is placed in %s code pool', $this->_expectedValue);
    }

    /**
     * Evaluates module is dependent on expected one
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateDepends($other)
    {
        if (!isset($other->depends) || !$other->depends->hasChildren()) {
            return false;
        }

        return isset($other->depends->{$this->_expectedValue});
    }

    /**
     * Text representation of module dependance
     *
     * @return string
     */
    protected function textDepends()
    {
        return sprintf('is dependent on %s module', $this->_expectedValue);
    }

    /**
     * Evaluates module version is equal to expected
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateVersion($other)
    {
        return $this->compareVersion($other, '=');
    }

    /**
     * Text representation of module version check
     *
     * @return string
     */
    protected function textVersion()
    {
        return sprintf('version is equal to %s', $this->_expectedValue);
    }

    /**
     * Evaluates module version is less than expected
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateVersionLessThan($other)
    {
        return $this->compareVersion($other, '<');
    }

    /**
     * Text representation of module version check
     *
     * @return string
     */
    protected function textVersionLessThan()
    {
        return sprintf('version is less than %s', $this->_expectedValue);
    }

    /**
     * Evaluates module version is greater than expected
     *
     * @param Varien_Simplexml_Element $other
     * @return boolean
     */
    protected function evaluateVersionGreaterThan($other)
    {
        return $this->compareVersion($other, '>');
    }

    /**
     * Text representation of module version check
     *
     * @return string
     */
    protected function textVersionGreaterThan()
    {
        return sprintf('version is greater than %s', $this->_expectedValue);
    }

    /**
     * Internal comparisment of the module version
     *
     * @param Varien_Simplexml_Element $other
     * @param string $operator
     */
    protected function compareVersion($other, $operator)
    {
        $this->setActualValue((string)$other->version);
        return version_compare($this->_actualValue, $this->_expectedValue, $operator);
    }

    /**
     * Custom failure description for showing config related errors
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
          'Failed asserting that %s module %s.', $this->_moduleName, $this->toString()
        );
    }
}
