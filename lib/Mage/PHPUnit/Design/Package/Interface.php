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
 * Interface for assertions in layout configuration
 *
 */
interface Mage_PHPUnit_Design_Package_Interface
{
    /**
     * Asserts layout file existance in design packages,
     * and returns actual and expected filenames as result
     *
     * @param string $fileName
     * @param string $area
     * @param string|null $designPackage if not specified any theme will be used
     * @param string|null $theme if not specified any theme will be used
     * @return array of 'expected' and 'actual' file names
     */
    public function getLayoutFileAssertion($fileName, $area, $designPackage = null, $theme = null);

}
