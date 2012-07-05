<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Varien
 * @package    Varien_Filter
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Varien_Filter_Template_Simple extends Varien_Object implements Zend_Filter_Interface
{
		protected $_startTag = '{{';
		protected $_endTag = '}}';

    /**
     * @todo
     *
     * @param $start
     * @param $end
     * @return
     */
		public function setTags($start, $end)
		{
			$this->_startTag = $start;
			$this->_endTag = $end;
			return $this;
		}

    /**
     * @todo
     *
     * @param $value
     * @return
     */
		public function filter($value)
		{
			return preg_replace('#'.$this->_startTag.'(.*?)'.$this->_endTag.'#e', '$this->getData("$1")', $value);
		}
}