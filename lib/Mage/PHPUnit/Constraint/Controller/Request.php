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
 * Constraint for controller request assetions
 *
 */
class Mage_PHPUnit_Constraint_Controller_Request extends Mage_PHPUnit_Constraint_Abstract
{
    const TYPE_ROUTE = 'route';
    const TYPE_ROUTE_NAME = 'route_name';
    const TYPE_CONTROLLER_NAME = 'controller_name';
    const TYPE_CONTROLLER_MODULE = 'controller_module';
    const TYPE_ACTION_NAME = 'action_name';
    const TYPE_DISPATCHED = 'dispatched';
    const TYPE_FORWARDED = 'forwarded';
    const TYPE_BEFORE_FORWARD_ROUTE = 'before_forward_route';

    /**
     * Constraint for controller request assetions
     *
     *
     * @param string $type
     * @param string|null $expectedValue
     */
    public function __construct($type, $expectedValue = null)
    {
        $this->_expectedValueValidation += array(
            self::TYPE_ROUTE => array(true, 'is_string', 'string'),
            self::TYPE_ROUTE_NAME => array(true, 'is_string', 'string'),
            self::TYPE_CONTROLLER_NAME => array(true, 'is_string', 'string'),
            self::TYPE_CONTROLLER_MODULE => array(true, 'is_string', 'string'),
            self::TYPE_ACTION_NAME => array(true, 'is_string', 'string'),
            self::TYPE_BEFORE_FORWARD_ROUTE => array(true, 'is_string', 'string')
        );

        $this->_typesWithDiff[] = self::TYPE_ROUTE;
        $this->_typesWithDiff[] = self::TYPE_ROUTE_NAME;
        $this->_typesWithDiff[] = self::TYPE_CONTROLLER_NAME;
        $this->_typesWithDiff[] = self::TYPE_CONTROLLER_MODULE;
        $this->_typesWithDiff[] = self::TYPE_ACTION_NAME;
        $this->_typesWithDiff[] = self::TYPE_BEFORE_FORWARD_ROUTE;

        parent::__construct($type, $expectedValue);
    }

    /**
     * Parses route to params
     *
     * @param string $route
     * @return array
     */
    protected function parseRoute($route)
    {
        $routeParts = explode('/', $route, 3);
        $routePartsCount = count($routeParts);
        if ($routePartsCount < 3) {
            array_pad($routeParts, 3-$routePartsCount, null);
        }

        $params = array();

        if ($routeParts[0] !== '*') {
            $params['route_name'] = !empty($routeParts[0]) ? $routeParts[0] : 'index';
        }

        if ($routeParts[1] !== '*') {
            $params['controller_name'] = !empty($routeParts[1]) ? $routeParts[1] : 'index';
        }

        if ($routeParts[2] !== '*') {
            $params['action_name'] = !empty($routeParts[2]) ? $routeParts[2] : 'index';
        }
        return $params;
    }

    /**
     * Evaluates that current controller route is equal to expected
     *
     * @param Mage_PHPUnit_Controller_Request_Interface $other
     * @return boolean
     */
    protected function evaluateRoute($other)
    {
        $params = $this->parseRoute($this->_expectedValue);
        $this->setActualValue(
            $other->getRouteName() . '/' . $other->getControllerName()
            . '/' . $other->getActionName()
        );
        foreach ($params as $propertyName => $expectedValue) {
            $methodName = 'get'.str_replace(' ', '',
                ucwords(strtr($propertyName, '_', ' '))
            );
            if ($other->$methodName() !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Text reperesentation of route path assertion
     *
     * @return string
     */
    protected function textRoute()
    {
        return 'route matches expected one';
    }

    /**
     * Evaluates that before forwarding controller route is equal to expected
     *
     * @param Mage_PHPUnit_Controller_Request_Interface $other
     * @return boolean
     */
    protected function evaluateBeforeForwardRoute($other)
    {
        if (!$other->getBeforeForwardInfo()) {
            $this->setActualValue(false);
            return false;
        }

        $params = $this->parseRoute($this->_expectedValue);
        $this->setActualValue(
            $other->getBeforeForwardInfo('route_name') . '/'
            . $other->getBeforeForwardInfo('controller_name') . '/'
            . $other->getBeforeForwardInfo('action_name')
        );

        foreach ($params as $propertyName => $expectedValue) {
            if ($other->getBeforeForwardInfo($propertyName) !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Text reperesentation of route path assertion
     *
     * @return string
     */
    protected function textBeforeForwardRoute()
    {
        return 'route before forwarding matches expected one';
    }

    /**
     * Evaluates that request was forwarded
     *
     * @param Mage_PHPUnit_Controller_Request_Interface $other
     * @return boolean
     */
    protected function evaluateForwarded($other)
    {
        return (bool)$other->getBeforeForwardInfo();
    }

    /**
     * Text reperesentation of was forwaded request assertion
     *
     * @return string
     */
    protected function textForwarded()
    {
        return 'is forwarded';
    }

    /**
     * Evaluates that request was forwarded
     *
     * @param Mage_PHPUnit_Controller_Request_Interface $other
     * @return boolean
     */
    protected function evaluateDispatched($other)
    {
        return $other->isDispatched();
    }

    /**
     * Text reperesentation of was forwaded request assertion
     *
     * @return string
     */
    protected function textDispatched()
    {
        return 'is dispatched';
    }

    /**
     * Evaluates that request route name is equal to expected
     *
     * @param Mage_PHPUnit_Controller_Request_Interface $other
     * @return boolean
     */
    protected function evaluateRouteName($other)
    {
        $this->setActualValue($other->getRouteName());
        return $this->_actualValue === $this->_expectedValue;
    }

    /**
     * Text reperesentation of route name assertion
     *
     * @return string
     */
    protected function textRouteName()
    {
        return 'route name is equal to expected';
    }

    /**
     * Evaluates that request controller name is equal to expected
     *
     * @param Mage_PHPUnit_Controller_Request_Interface $other
     * @return boolean
     */
    protected function evaluateControllerName($other)
    {
        $this->setActualValue($other->getControllerName());
        return $this->_actualValue === $this->_expectedValue;
    }

    /**
     * Text reperesentation of controller name assertion
     *
     * @return string
     */
    protected function textControllerName()
    {
        return 'controller name is equal to expected';
    }

    /**
     * Evaluates that request controller module is equal to expected
     *
     * @param Mage_PHPUnit_Controller_Request_Interface $other
     * @return boolean
     */
    protected function evaluateControllerModule($other)
    {
        $this->setActualValue($other->getControllerModule());
        return $this->_actualValue === $this->_expectedValue;
    }

    /**
     * Text reperesentation of controller module assertion
     *
     * @return string
     */
    protected function textControllerModule()
    {
        return 'controller module is equal to expected';
    }

    /**
     * Evaluates that request action name is equal to expected
     *
     * @param Mage_PHPUnit_Controller_Request_Interface $other
     * @return boolean
     */
    protected function evaluateActionName($other)
    {
        $this->setActualValue($other->getActionName());
        return $this->_actualValue === $this->_expectedValue;
    }

    /**
     * Text reperesentation of action name assertion
     *
     * @return string
     */
    protected function textActionName()
    {
        return 'action name is equal to expected';
    }

    /**
     * Custom failure description for showing request related errors
     * (non-PHPdoc)
     * @see PHPUnit_Framework_Constraint::customFailureDescription()
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
            'Failed asserting that request %s.',
            $this->toString()
        );
    }
}
