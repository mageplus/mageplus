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
 * Constraint for main layout functionality
 *
 */
class Mage_PHPUnit_Constraint_Layout extends Mage_PHPUnit_Constraint_Layout_Abstract
{
    const TYPE_LOADED = 'loaded';
    const TYPE_RENDERED = 'rendered';

    const ACTION_RENDER = Mage_PHPUnit_Constraint_Layout_Logger_Interface::ACTION_RENDER;

    /**
     * Constraint for main layout functions
     *
     * @param string $type
     */
    public function __construct($type = self::TYPE_LOADED)
    {
        parent::__construct($type);
    }

    /**
     * Evaluates that layout was loaded
     *
     * @param Mage_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return boolean
     */
    protected function evaluateLoaded($other)
    {
        return $other->isLoaded();
    }

    /**
     * Text representation of layout is loaded assertion
     *
     * @return string
     */
    protected function textLoaded()
    {
        return 'is loaded';
    }

    /**
     * Evaluates that layout was rendered
     *
     *
     * @param Mage_PHPUnit_Constraint_Layout_Logger_Interface $other
     * @return boolean
     */
    protected function evaluateRendered($other)
    {
        return $other->findFirst(self::ACTION_RENDER, 'layout') !== false;
    }

    /**
     * Text representation of layout is rendered assertion
     *
     * @return string
     */
    protected function textRendered()
    {
        return 'is rendered';
    }
}
