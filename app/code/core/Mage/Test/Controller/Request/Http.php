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
 * @package    Mage_Test
 * @copyright  Copyright (c) 2012 EcomDev BV (http://www.ecomdev.org)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan.chepurnyi@ecomdev.org>
 */

/**
 * Request class for usage in the controller test cases
 *
 * By default set for test app instance,
 * you can change to your class,
 * but you should extend it from this one
 *
 */
class Mage_Test_Controller_Request_Http
    extends Mage_Core_Controller_Request_Http
    implements Mage_PHPUnit_Isolation_Interface,
               Mage_PHPUnit_Controller_Request_Interface
{
    /**
     * List of $_SERVER variable changes
     * that were done by test case
     *
     * @var array
     */
    protected $_originalServerValues = array();

    /**
     * List of headers that were set for test case
     *
     * @return array
     */
    protected $_headers = array();

    /**
     * Initializes forward data
     *
     * (non-PHPdoc)
     * @see Mage_Core_Controller_Request_Http::initForward()
     */
    public function initForward()
    {
        if (empty($this->_beforeForwardInfo)) {
            parent::initForward();
            $this->_beforeForwardInfo['route_name'] = $this->getRouteName();
            return $this;
        }

        return parent::initForward();
    }

    /**
     * Returns only request uri that was set before
     * (non-PHPdoc)
     * @see Zend_Controller_Request_Http::getRequestUri()
     */
    public function getRequestUri()
    {
        return $this->_requestUri;
    }

    /**
     * Sets cookie value for test,
     *
     * @param string|array $name
     * @param string|null $value
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function setCookie($name, $value)
    {
        $_COOKIE[$name] = $value;
        return $this;
    }

    /**
     * Sets more than one cookie
     *
     * @param array $cookies
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function setCookies(array $cookies)
    {
        $_COOKIE += $cookies;
        return $this;
    }

    /**
     * Resets all cookies for the test request
     *
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function resetCookies()
    {
        $_COOKIE = array();
        return $this;
    }

    /**
     * Resets query for the current request
     *
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function resetQuery()
    {
        $_GET = array();
        return $this;
    }

    /**
     * Resets $_POST superglobal for test request
     *
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function resetPost()
    {
        $_POST = array();
        return $this;
    }

    /**
     * Resets user defined request params for test request
     *
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function resetParams()
    {
        $this->_params = array();
        return $this;
    }

    /**
     * Resets internal properties to its default values
     *
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function resetInternalProperties()
    {
        // From abstract request
        $this->_dispatched = false;
        $this->_module = null;
        $this->_moduleKey = 'module';
        $this->_controller = null;
        $this->_controllerKey = 'controller';
        $this->_action = null;
        $this->_actionKey = 'action';

        // From Http request
        $this->_paramSources = array('_GET', '_POST');
        $this->_requestUri = null;
        $this->_baseUrl = null;
        $this->_basePath = null;
        $this->_pathInfo = '';
        $this->_rawBody = null;
        $this->_aliases = array();

        // From Magento Http request
        $this->_originalPathInfo = '';
        $this->_storeCode = null;
        $this->_requestString = '';
        $this->_rewritedPathInfo = null;
        $this->_requestedRouteName = null;
        $this->_routingInfo = array();
        $this->_route = null;
        $this->_directFrontNames = null;
        $this->_controllerModule = null;
        return $this;
    }

    /**
     * Set custom http header
     *
     * @param string $name
     * @param string $value
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function setHeader($name, $value)
    {
        $name = $this->headerName($name);
        $this->_headers[$name] = $value;
        // Additionally set $_SERVER http header value
        $this->setServer('HTTP_' . $name, $value);
        return $this;
    }

    /**
     * Sets more than one header,
     * headers list is an associative array
     *
     * @param array $headers
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * Returns header from test request parameters
     *
     * (non-PHPdoc)
     * @see Zend_Controller_Request_Http::getHeader()
     */
    public function getHeader($header)
    {
        $name = $this->headerName($header);
        if (isset($this->_headers[$name])) {
            return $this->_headers[$name];
        }

        return false;
    }

    /**
     * Resets headers in test request
     *
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function resetHeaders()
    {
        $this->_headers = array();
        return $this;
    }

    /**
     * Returns unified header name for internal storage
     *
     * @param string $name
     * @return string
     */
    protected function headerName($name)
    {
        return strtr(strtoupper($name), '-', '_');
    }

    /**
     * Sets value for a particular $_SERVER superglobal array key for test request
     *
     * Saves original value for returning it back
     *
     * @param string $name
     * @param string $value
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function setServer($name, $value)
    {
        if (!isset($this->_originalServerValues[$name])) {
            $originalValue = (isset($_SERVER[$name]) ? $_SERVER[$name] : null);
            $this->_originalServerValues[$name] = $originalValue;
        }

        $_SERVER[$name] = $value;
        return $this;
    }

    /**
     * Sets multiple values for $_SERVER superglobal in test request
     *
     * @param array $values
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function setServers(array $values)
    {
        foreach ($values as $name => $value) {
            $this->setServer($name, $value);
        }
        return $this;
    }

    /**
     * Resets $_SERVER superglobal to previous state
     *
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function resetServer()
    {
        foreach ($this->_originalServerValues as $name => $value) {
            if ($value !== null) {
                $_SERVER[$name] = $value;
            } elseif (isset($_SERVER[$name])) {
                // If original value was not set,
                // then unsetting the changed value
                unset($_SERVER[$name]);
            }
        }

        $this->_originalServerValues = array();
        return $this;
    }

    /**
     * Sets request method for test request
     *
     * @param string $requestMethod
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function setMethod($requestMethod)
    {
        $this->setServer('REQUEST_METHOD', $requestMethod);
        return $this;
    }

    /**
     * Sets current request scheme for test request,
     * accepts boolean flag for HTTPS
     *
     * @param boolean $flag
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function setIsSecure($flag = true)
    {
        $this->setServer('HTTPS', $flag ? 'on' : null);
        return $this;
    }

    /**
     * Returns HTTP host from base url that were set in the controller
     *
     * (non-PHPdoc)
     * @see Mage_Core_Controller_Request_Http::getHttpHost()
     */
    public function getHttpHost($trimPort = false)
    {
        $baseUrl = $this->getBaseUrl();

        $parts = parse_url($baseUrl);
        $httpHost = $parts['host'];
        if (!$trimPort && isset($parts['port'])) {
            $httpHost .= ':' . $parts['port'];
        }

        return $httpHost;
    }

    /**
     * Returns only base url that was set before
     *
     * (non-PHPdoc)
     * @see Mage_Core_Controller_Request_Http::getBaseUrl()
     */
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    /**
     * Resets all request data for test
     *
     * @return Mage_Test_Controller_Request_Http_Test
     */
    public function reset()
    {
        $this->resetInternalProperties()
            ->resetHeaders()
            ->resetParams()
            ->resetPost()
            ->resetQuery()
            ->resetCookies()
            ->resetServer();

        return $this;
    }
}
