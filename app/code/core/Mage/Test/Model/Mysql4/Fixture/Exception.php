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

class Mage_Test_Model_Mysql4_Fixture_Exception extends RuntimeException
{
	/**
	 * @todo
	 *
	 * @param $message
	 * @param Exception $previous
	 * @return
	 */
	public function __construct($message, Exception $previous)
	{
		parent::__construct($message, 0, $previous);
	}
}