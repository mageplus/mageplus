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
 * Constraint for controller response body assertions
 *
 */
class Mage_PHPUnit_Constraint_Controller_Response_Body
    extends Mage_PHPUnit_Constraint_Controller_Response_Abstract
{
    const TYPE_CONSTRAINT = 'constraint';

    /**
     * Constraint for controller response body assertions
     *
     * @param PHPUnit_Framework_Constraint $constraint
     * @param string $type
     */
    public function __construct(PHPUnit_Framework_Constraint $constraint = null, $type = self::TYPE_CONSTRAINT)
    {
        $this->_expectedValueValidation += array(
            self::TYPE_CONSTRAINT => array(true, null, 'PHPUnit_Framework_Constraint')
        );

        parent::__construct($type, $constraint);
    }

    /**
     * Evaluates controller response body is evaluated by constraint
     *
     *
     * @param Mage_PHPUnit_Controller_Response_Interface $other
     */
    protected function evaluateConstraint($other)
    {
        $this->setActualValue($other->getOutputBody());
        return $this->_expectedValue->evaluate($this->_actualValue);
    }

    /**
     * Text representation of response body is evaluated by constraint assertion
     *
     * @return string
     */
    protected function textConstraint()
    {
        return sprintf('body %s', $this->_expectedValue->toString());
    }
}