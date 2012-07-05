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
 * @package    Varien_Io
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Install and upgrade client abstract class
 *
 * @category   Varien
 * @package    Varien_Io
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Varien_Io_Abstract implements Varien_Io_Interface
{
    /**
     * If this variable is set to true, our library will be able to automaticaly
     * create non-existant directories
     *
     * @var bool
     */
    protected $_allowCreateFolders = false;

    /**
     * Allow automaticaly create non-existant directories
     *
     * @param bool $flag
     * @return Varien_Io_Abstract
     */
    public function setAllowCreateFolders($flag)
    {
        $this->_allowCreateFolders = (bool)$flag;
        return $this;
    }

    /**
     * Open a connection
     *
     * @param array $config
     * @return bool
     */
    public function open(array $args = array())
    {
        return false;
    }

    /**
     * @todo
     *
     * @return
     */
    public function dirsep()
    {
        return '/';
    }

    /**
     * @todo
     *
     * @param $path
     * @return
     */
    public function getCleanPath($path)
    {
        if (empty($path)) {
            return './';
        }

        $path = trim(preg_replace("/\\\\/", "/", (string)$path));

        if (!preg_match("/(\.\w{1,4})$/", $path) && !preg_match("/\?[^\\/]+$/", $path) && !preg_match("/\\/$/", $path)) {
            $path .= '/';
        }

        $matches = array();
        $pattern = "/^(\\/|\w:\\/|https?:\\/\\/[^\\/]+\\/)?(.*)$/i";
        preg_match_all($pattern, $path, $matches, PREG_SET_ORDER);

        $pathTokR = $matches[0][1];
        $pathTokP = $matches[0][2];

        $pathTokP = preg_replace(array("/^\\/+/", "/\\/+/"), array("", "/"), $pathTokP);

        $pathParts = explode("/", $pathTokP);
        $realPathParts = array();

        for ($i = 0, $realPathParts = array(); $i < count($pathParts); $i++) {
            if ($pathParts[$i] == '.') {
                continue;
            }
            elseif ($pathParts[$i] == '..') {
                if ((isset($realPathParts[0])  &&  $realPathParts[0] != '..') || ($pathTokR != "")) {
                    array_pop($realPathParts);
                    continue;
                }
            }

            array_push($realPathParts, $pathParts[$i]);
        }

        return $pathTokR . implode('/', $realPathParts);
    }

    /**
     * @todo
     *
     * @param $haystackPath
     * @param $needlePath
     * @return
     */
    public function allowedPath($haystackPath, $needlePath)
    {
        return strpos($this->getCleanPath($haystackPath), $this->getCleanPath($needlePath)) === 0;
    }
}