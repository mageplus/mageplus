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
 * Interface for response object, that will be asserted
 *
 */
interface Mage_PHPUnit_Controller_Response_Interface
{
    /**
     * Returns rendered headers array that was sent,
     * if headers was not sent, then returns null
     *
     * @return array|null
     */
    public function getSentHeaders();

    /**
     * Returns a particular header that was sent
     *
     * @param string $headerName
     * @return string|false
     */
    public function getSentHeader($headerName);

    /**
     * Returns rendered response, if response was not sent,
     * then it returns null
     *
     * @return string|null
     */
    public function getSentResponse();

    /**
     * Returns rendered body output
     *
     * @return string
     */
    public function getOutputBody();
}
