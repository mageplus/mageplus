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
 * Constaint related to main layout block action calls functionality
 *
 */
class EcomDev_PHPUnit_Constraint_Layout_Block_Action extends EcomDev_PHPUnit_Constraint_Layout_Abstract
{
    const TYPE_INVOKED = 'invoked';
    const TYPE_INVOKED_AT_LEAST = 'invoked_at_least';
    const TYPE_INVOKED_EXACTLY = 'invoked_exactly';

    const ACTION_BLOCK_ACTION = EcomDev_PHPUnit_Constraint_Layout_Logger_Interface::ACTION_BLOCK_ACTION;

    const SEARCH_TYPE_OR = EcomDev_PHPUnit_Constraint_Layout_Logger_Interface::SEARCH_TYPE_OR;
    const SEARCH_TYPE_EXACT = EcomDev_PHPUnit_Constraint_Layout_Logger_Interface::SEARCH_TYPE_EXACT;
    const SEARCH_TYPE_AND = EcomDev_PHPUnit_Constraint_Layout_Logger_Interface::SEARCH_TYPE_AND;

    /**
     * Block name for the action
     *
     * @var string
     */
    protected $_blockName = null;

    /**
     * Block method name for the action
     *
     * @var string
     */
    protected $_method = null;

    /**
     * Target for searching in layout records
     *
     * @var string
     */
    protected $_target = null;

    /**
     * Block method arguments for search
     *
     * @var array
     */
    protected $_arguments = null;

    /**
     * Block method arguments search type
     *
     * @var unknown_type
     */
    protected $_searchType = self::SEARCH_TYPE_AND;

    /**
     * Constaint related to main layout block action calls functionality
     *
     * @param string $blockName
     * @param string $method
     * @param string $type
     * @param int|null $invocationCount
     * @param array|null $parameters
     * @param string $searchType
     */
    public function __construct($blockName, $method, $type, $invocationCount = null,
        array $arguments = null, $searchType = self::SEARCH_TYPE_AND)
    {
        if (empty($blockName) || !is_string($blockName)) {
            PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string', $blockName);
        }

        if (empty($method) || !is_string($method)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string', $method);
        }

        if (!is_string($searchType)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(6, 'string', $searchType);
        }

        $this->_expectedValueValidation += array(
            self::TYPE_INVOKED_AT_LEAST => array(true, 'is_int', 'integer'),
            self::TYPE_INVOKED_EXACTLY => array(true, 'is_int', 'integer')
        );

        parent::__construct($type, $invocationCount);

        $this->_blockName = $blockName;
        $this->_method = $method;
        $this->_target = sprintf('%s::%s', $this->_blockName, $this->_method);
        $this->_arguments = $arguments;
    }

    /**
     * Finds records in layout logger history
     *
     * @param EcomDev_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return array
     */
    protected function findRecords($other)
    {
        if ($this->_arguments !== null) {
            $this->setActualValue($other->findAll(self::ACTION_BLOCK_ACTION, $this->_target));
            return $other->findByParameters(
                self::ACTION_BLOCK_ACTION, $this->_target,
                $this->_arguments, $this->_searchType
            );
        }
        $records = $other->findAll(self::ACTION_BLOCK_ACTION, $this->_target);
        $this->setActualValue($records);
        return $records;
    }

    /**
     * Evaluates that method was invoked at least once
     *
     * @param EcomDev_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return boolean
     */
    protected function evaluateInvoked($other)
    {
        $records = $this->findRecords($other);
        return !empty($records);
    }

    /**
     * Text representation of at least once invokation
     *
     * @return string
     */
    protected function textInvoked()
    {
        $withArguments = '';

        if ($this->_arguments !== null) {
            $withArguments = ' with expected arguments';
        }

        return sprintf('block "%s" action for method "%s" was invoked%s',
                       $this->_blockName, $this->_method, $withArguments);
    }

    /**
     * Evaluates that method was invoked
     * at least expected number of times
     *
     * @param EcomDev_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return boolean
     */
    protected function evaluateInvokedAtLeast($other)
    {
        $records = $this->findRecords($other);
        return count($records) >= $this->_expectedValue;
    }

    /**
     * Text representation of at least expected times invokation
     *
     * @return string
     */
    protected function textInvokedAtLeast()
    {
        return $this->textInvoked() . sprintf(' at least %d times', $this->_expectedValue);
    }

    /**
     * Evaluates that method was invoked
     * exactly expected number of times
     *
     * @param EcomDev_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return boolean
     */
    protected function evaluateInvokedExactly($other)
    {
        $records = $this->findRecords($other);
        return count($records) === $this->_expectedValue;
    }

    /**
     * Text representation of exactly times invokation
     *
     * @return string
     */
    protected function textInvokedExactly()
    {
        return $this->textInvoked() . sprintf(' exactly %d times', $this->_expectedValue);
    }
}
