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
 * Constraint for testing layout handles load priority
 *
 */
class Mage_PHPUnit_Constraint_Layout_Handle extends Mage_PHPUnit_Constraint_Layout_Abstract
{
    const TYPE_LOADED = 'loaded';
    const TYPE_LOADED_AFTER = 'loaded_after';
    const TYPE_LOADED_BEFORE = 'loaded_before';

    const ACTION_HANDLE_LOADED =  Mage_PHPUnit_Constraint_Layout_Logger_Interface::ACTION_HANDLE_LOADED;

    /**
     * Position element of layout handle,
     * for instance another handle
     *
     * @var string|null
     */
    protected $_position = null;

    /**
     * Handle name
     *
     * @var string
     */
    protected $_handle = null;

    /**
     * Layout handle constraint
     *
     * @param string $handle layout handle name
     * @param string $type
     * @param string|null $position layout handle position
     */
    public function __construct($handle, $type, $position = null)
    {
        if ($position !== null && !is_string($position)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'string', $position);
        }

        $this->_position = $position;

        $this->_handle = $handle;

        $this->_expectedValueValidation += array(
            self::TYPE_LOADED => array(true, 'is_string', 'string'),
            self::TYPE_LOADED_AFTER => array(true, 'is_string', 'string'),
            self::TYPE_LOADED_BEFORE => array(true, 'is_string', 'string')
        );

        $this->_typesWithDiff[] = self::TYPE_LOADED;
        $this->_typesWithDiff[] = self::TYPE_LOADED_AFTER;
        $this->_typesWithDiff[] = self::TYPE_LOADED_BEFORE;

        parent::__construct($type, $this->_handle);
    }

    /**
     * Evaluates that layout handle was loaded
     *
     * @param Mage_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return boolean
     */
    protected function evaluateLoaded($other)
    {
        $this->setActualValue(
            $other->findAllTargets(self::ACTION_HANDLE_LOADED)
        );

        $this->_expectedValue = $this->_actualValue;

        $match = $other->findFirst(self::ACTION_HANDLE_LOADED, $this->_handle) !== false;

        if (!$match) {
            $this->_expectedValue[] = $this->_handle;
        }

        return $match;
    }

    /**
     * Text representation of layout handle loaded assertion
     *
     * @return string
     */
    protected function textLoaded()
    {
        return sprintf('handle "%s" is loaded', $this->_handle);
    }

    /**
     * Evaluates that layout handle was loaded after another
     *
     * @param Mage_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return boolean
     */
    protected function evaluateLoadedAfter($other)
    {
        $handleInfo = $other->findFirst(self::ACTION_HANDLE_LOADED, $this->_handle);

        if ($handleInfo === false) {
            return false;
        }

        $match = in_array($this->_position, $handleInfo['after']);

        $this->setActualValue(
            $handleInfo['before']
        );

        $this->_actualValue[] = $this->_handle;
        $this->_expectedValue = $this->_actualValue;

        if (!$match) {
            array_splice(
                $this->_expectedValue,
                array_search($this->_handle, $this->_expectedValue),
                0,
                $this->_position
            );
        }

        return $match;
    }

    /**
     * Text representation of loaded after assertion
     *
     * @return string
     */
    protected function textLoadedAfter()
    {
        return sprintf('handle "%s" is loaded after "%s"', $this->_handle, $this->_position);
    }

    /**
     * Evaluates that layout handle was loaded after another
     *
     * @param Mage_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return boolean
     */
    protected function evaluateLoadedBefore($other)
    {
        $handleInfo = $other->findFirst(self::ACTION_HANDLE_LOADED, $this->_handle);

        if ($handleInfo === false) {
            return false;
        }

        $match = in_array($this->_position, $handleInfo['before']);


        $this->setActualValue(
            $handleInfo['before']
        );

        array_unshift($this->_actualValue, $this->_handle);
        $this->_expectedValue = $this->_actualValue;

        if (!$match) {
            array_splice(
                $this->_expectedValue,
                array_search($this->_handle, $this->_expectedValue) + 1,
                0,
                $this->_position
            );
        }

        return $match;
    }

    /**
     * Text representation of loaded after assertion
     *
     * @return string
     */
    protected function textLoadedBefore()
    {
        return sprintf('handle "%s" is loaded before "%s"', $this->_handle, $this->_position);
    }
}
