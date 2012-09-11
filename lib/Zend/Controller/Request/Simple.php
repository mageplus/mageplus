<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Request
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Simple.php 24593 2012-01-05 20:35:02Z matthew $
 */

/** Zend_Controller_Request_Abstract */
require_once 'Zend/Controller/Request/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Request
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Request_Simple extends Zend_Controller_Request_Abstract
{

    public function __construct($action = null, $controller = null, $module = null, array $params = array())
    {
        if ($action) {
            $this->setActionName($action);
        }

        if ($controller) {
            $this->setControllerName($controller);
        }

        if ($module) {
            $this->setModuleName($module);
        }

        if ($params) {
            $this->setParams($params);
        }
    }

}
