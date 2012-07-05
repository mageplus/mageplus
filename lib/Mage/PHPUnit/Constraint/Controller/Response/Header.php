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
 * Constraint for controller response header assertions
 *
 */
class Mage_PHPUnit_Constraint_Controller_Response_Header
    extends Mage_PHPUnit_Constraint_Controller_Response_Abstract
{
    const TYPE_CONSTRAINT = 'constraint';
    const TYPE_SENT = 'sent';

    /**
     * The name of the header that will be asserted
     *
     * @var string
     */
    protected $_headerName = null;

    /**
     * Response header assertion
     *
     * @param string $headerName
     * @param string $type
     * @param PHPUnit_Framework_Constraint $constraint
     */
    public function __construct($headerName, $type, PHPUnit_Framework_Constraint $constraint = null)
    {
        if (empty($headerName) || !is_string($headerName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string', $headerName);
        }

        $this->_expectedValueValidation += array(
            self::TYPE_CONSTRAINT => array(true, null, 'PHPUnit_Framework_Constraint')
        );

        parent::__construct($type, $constraint);
        $this->_headerName = $headerName;
    }

    /**
     * Evaluates controller response header is sent
     *
     *
     * @param Mage_PHPUnit_Controller_Response_Interface $other
     */
    protected function evaluateSent($other)
    {
        $this->setActualValue($other->getSentHeaders());
        return $other->getSentHeader($this->_headerName) !== false;
    }

    /**
     * Text representation of header is sent assertion
     *
     * @return string
     */
    protected function textSent()
    {
        return sprintf('header "%s" is sent', $this->_headerName);
    }

    /**
     * Evaluates controller response header is evaluated by constraint
     *
     *
     * @param Mage_PHPUnit_Controller_Response_Interface $other
     */
    protected function evaluateConstraint($other)
    {
        $this->setActualValue($other->getSentHeader($this->_headerName));
        return $this->_expectedValue->evaluate($this->_actualValue);
    }

    /**
     * Text representation of header is evaluated by constraint assertion
     *
     * @return string
     */
    protected function textConstraint()
    {
        return sprintf('header "%s" value %s', $this->_headerName, $this->_expectedValue->toString());
    }
}
