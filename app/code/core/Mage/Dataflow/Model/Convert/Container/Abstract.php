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
 * @category    Mage
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Convert container abstract
 *
 * @category   Mage
 * @package    Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Dataflow_Model_Convert_Container_Abstract
    implements Mage_Dataflow_Model_Convert_Container_Interface
{
    protected $_batchParams = array();

    protected $_vars;

    protected $_profile;

    protected $_action;

    protected $_data;

    protected $_position;

    /**
     * @todo
     *
     * @param $key
     * @param $default
     * @return
     */
    public function getVar($key, $default=null)
    {
        if (!isset($this->_vars[$key]) || (!is_array($this->_vars[$key]) && strlen($this->_vars[$key]) == 0)) {
            return $default;
        }
        return $this->_vars[$key];
    }

    /**
     * @todo
     * @return
     */
    public function getVars()
    {
        return $this->_vars;
    }

    /**
     * @todo
     *
     * @param $key
     * @param $value
     * @return
     */
    public function setVar($key, $value=null)
    {
        if (is_array($key) && is_null($value)) {
            $this->_vars = $key;
        } else {
            $this->_vars[$key] = $value;
        }
        return $this;
    }

    /**
     * @todo
     * @return
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @todo
     *
     * @param Mage_Dataflow_Model_Convert_Action_Interface $action
     * @return
     */
    public function setAction(Mage_Dataflow_Model_Convert_Action_Interface $action)
    {
        $this->_action = $action;
        return $this;
    }

    /**
     * @todo
     * @return
     */
    public function getProfile()
    {
        return $this->_profile;
    }

    /**
     * @todo
     *
     * @param Mage_Dataflow_Model_Convert_Profile_Interface $profile
     * @return
     */
    public function setProfile(Mage_Dataflow_Model_Convert_Profile_Interface $profile)
    {
        $this->_profile = $profile;
        return $this;
    }

    /**
     * @todo
     * @return
     */
    public function getData()
    {
        if (is_null($this->_data) && $this->getProfile()) {
            $this->_data = $this->getProfile()->getContainer()->getData();
        }
        return $this->_data;
    }

    /**
     * @todo
     *
     * @param $data
     * @return
     */
    public function setData($data)
    {
        if ($this->getProfile()) {
            $this->getProfile()->getContainer()->setData($data);
        }
        $this->_data = $data;
        return $this;
    }

    /**
     * @todo
     *
     * @param $data
     * @return
     */
    public function validateDataString($data=null)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        if (!is_string($data)) {
            $this->addException("Invalid data type, expecting string.", Mage_Dataflow_Model_Convert_Exception::FATAL);
        }
        return true;
    }

    /**
     * @todo
     *
     * @param $data
     * @return
     */
    public function validateDataArray($data=null)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        if (!is_array($data)) {
            $this->addException("Invalid data type, expecting array.", Mage_Dataflow_Model_Convert_Exception::FATAL);
        }
        return true;
    }

    /**
     * @todo
     *
     * @param $data
     * @return
     */
    public function validateDataGrid($data=null)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        if (!is_array($data) || !is_array(current($data))) {
            if (count($data)==0) {
                return true;
            }
            $this->addException("Invalid data type, expecting 2D grid array.", Mage_Dataflow_Model_Convert_Exception::FATAL);
        }
        return true;
    }

    /**
     * @todo
     *
     * @param $grid
     * @return
     */
    public function getGridFields($grid)
    {
        $fields = array();
        foreach ($grid as $i=>$row) {
            foreach ($row as $fieldName=>$data) {
                if (!in_array($fieldName, $fields)) {
                    $fields[] = $fieldName;
                }
            }
        }
        return $fields;
    }

    /**
     * @todo
     *
     * @param $error
     * @param $level
     * @return
     */
    public function addException($error, $level=null)
    {
        $e = new Mage_Dataflow_Model_Convert_Exception($error);
        $e->setLevel(!is_null($level) ? $level : Mage_Dataflow_Model_Convert_Exception::NOTICE);
        $e->setContainer($this);
        $e->setPosition($this->getPosition());

        if ($this->getProfile()) {
            $this->getProfile()->addException($e);
        }

        return $e;
    }

    /**
     * @todo
     * @return
     */
    public function getPosition()
    {
        return $this->_position;
    }

    /**
     * @todo
     *
     * @param $position
     * @return
     */
    public function setPosition($position)
    {
        $this->_position = $position;
        return $this;
    }

    /**
     * @todo
     *
     * @param $data
     * @return
     */
    public function setBatchParams($data)
    {
        if (is_array($data)) {
            $this->_batchParams = $data;
        }
        return $this;
    }

    /**
     * @todo
     *
     * @param $key
     * @return
     */
    public function getBatchParams($key = null)
    {
        if (!empty($key)) {
            return isset($this->_batchParams[$key]) ? $this->_batchParams[$key] : null;
        }
        return $this->_batchParams;
    }
}