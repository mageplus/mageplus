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
 * Response class for usage in the controller test cases
 *
 * By default set for test app instance,
 * you can change to your class,
 * but you should extend it from this one
 *
 */
class Mage_Test_Controller_Response_Http
    extends Mage_Core_Controller_Response_Http
    implements Mage_PHPUnit_Isolation_Interface,
               Mage_PHPUnit_Controller_Response_Interface
{
    const LINE_ENDING = "\r\n";

    /**
     * Headers that was sent via sendHeaders() method
     *
     * @var array
     */
    protected $_sentHeaders = null;

    /**
     * Response that was sent via sendRespose()
     * or sendHeaders() or outputBody() methods
     *
     * @var string
     */
    protected $_sentResponse = null;

    /**
     * Response body that was sent via outputBody()
     * method
     *
     * @var string
     */
    protected $_outputBody = null;

    /**
     * Resets response object
     *
     * @return Mage_Test_Controller_Response_Http_Test
     */
    public function reset()
    {
        $this->_sentHeaders = null;
        $this->_sentResponse = null;
        $this->_outputBody = null;
        $this->_body = array();
        $this->_exceptions = array();
        $this->_headers = array();
        $this->_headersRaw = array();
        $this->_httpResponseCode = 200;
        $this->_isRedirect = false;
        $this->_renderExceptions = false;

        $this->headersSentThrowsException = Mage::$headersSentThrowsException;
        $this->setHeader('Content-Type', 'text/html; charset=UTF-8');
        return $this;
    }

    /**
     * Implementation of sending the headers to output
     *
     * (non-PHPdoc)
     * @see Mage_Core_Controller_Response_Http::sendHeaders()
     */
    public function sendHeaders()
    {
        $this->canSendHeaders(true);
        $this->_sentHeaders= array();

        $this->_sentHeaders[null] = 'HTTP/1.1 ' . $this->_httpResponseCode;

        foreach ($this->_headersRaw as $headerRaw) {
            list($headerName, $headerValue) = explode(':', $headerRaw, 2);
            $headerName = $this->_normalizeHeader($headerName);
            if (isset($this->_sentHeaders[$headerName])) {
                // Merge headers, if already any was sent with the same name
                // Set-Cookie header is usually not sent via response
                // so it should be ok.
                $this->_sentHeaders[$headerName] .= '; ' . $headerValue;
            } else {
                $this->_sentHeaders[$headerName] = $headerValue;
            }
        }

        foreach ($this->_headers as $header) {
            if (isset($this->_sentHeaders[$header['name']])) {
                $this->_sentHeaders[$header['name']] .=  '; ' . $header['value'];
            } else {
                $this->_sentHeaders[$header['name']] = $header['value'];
            }
        }

        $this->_sentResponse = '';

        foreach ($this->_sentHeaders as $headerName => $headerValue) {
            $headerString = '';
            if ($headerName === null) {
                $headerString .= $headerName . ': ';
            }
            $headerString .= $headerValue . self::LINE_ENDING;
            $this->_sentResponse .= $headerString;
        }

        $this->_sentResponse .= self::LINE_ENDING;
    }

    /**
     * Returns rendered headers array that was sent,
     * if headers was not sent, then returns null
     *
     * @return array|null
     */
    public function getSentHeaders()
    {
        return $this->_sentHeaders;
    }

    /**
     * Returns a particular header that was sent
     *
     * @param string $headerName
     * @return string|false
     */
    public function getSentHeader($headerName)
    {
        $headerName = $this->_normalizeHeader($headerName);

        if (isset($this->_sentHeaders[$headerName])) {
            return $this->_sentHeaders[$headerName];
        }

        return false;
    }

    /**
     * Implementation of sending response for test case
     * (non-PHPdoc)
     * @see Mage_Core_Controller_Response_Http::sendResponse()
     */
    public function sendResponse()
    {
        //Mage::dispatchEvent('http_response_send_before', array('response'=>$this));
        $this->sendHeaders();

        if ($this->isException() && $this->renderExceptions()) {
            $exceptions = '';
            foreach ($this->getException() as $e) {
                $exceptions .= (string)$e . "\n";
            }

            $this->_sentResponse .= $exceptions;
            return;
        }

        $this->outputBody();
    }

    /**
     * Returns rendered response, if response was not sent,
     * then it returns null
     *
     * @return string|null
     */
    public function getSentResponse()
    {
        return $this->_sentResponse;
    }

    /**
     * Implementation of outputting the body of response
     *
     * (non-PHPdoc)
     * @see Zend_Controller_Response_Abstract::outputBody()
     */
    public function outputBody()
    {
        $this->_outputBody = implode('', $this->_body);
        $this->_sentResponse .= $this->_outputBody;
    }

    /**
     * Returns rendered body output
     *
     * @return string
     */
    public function getOutputBody()
    {
        return $this->_outputBody;
    }

    /**
     * Can send headers implementation for test case
     *
     * (non-PHPdoc)
     * @see Zend_Controller_Response_Abstract::canSendHeaders()
     */
    public function canSendHeaders($throw = false)
    {
        if ($this->_sentHeaders !== null && $throw && $this->headersSentThrowsException) {
            #require_once 'Zend/Controller/Response/Exception.php';
            throw new Zend_Controller_Response_Exception('Cannot send headers; headers already sent');
        }

        return $this->_sentHeaders === null;
    }
}