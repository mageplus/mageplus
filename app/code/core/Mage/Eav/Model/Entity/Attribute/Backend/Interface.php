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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Entity attribute backend interface
 *
 * Backend is responsible for saving the values of the attribute
 * and performing pre and post actions
 *
 */
interface Mage_Eav_Model_Entity_Attribute_Backend_Interface
{
    /**
     * @todo
     *
     * @return
     */
    public function getTable();
    
    /**
     * @todo
     *
     * @return
     */
    public function isStatic();
    
    /**
     * @todo
     *
     * @return
     */
    public function getType();
    
    /**
     * @todo
     *
     * @return
     */
    public function getEntityIdField();
    
    /**
     * @todo
     *
     * @param $valueId
     *
     * @return
     */
    public function setValueId($valueId);
    
    /**
     * @todo
     *
     * @return
     */
    public function getValueId();
    
    /**
     * @todo
     *
     * @param $object
     *
     * @return
     */
    public function afterLoad($object);
    
    /**
     * @todo
     *
     * @param $object
     *
     * @return
     */
    public function beforeSave($object);
    
    /**
     * @todo
     *
     * @param $object
     *
     * @return
     */
    public function afterSave($object);
    
    /**
     * @todo
     *
     * @param $object
     *
     * @return
     */
    public function beforeDelete($object);
    
    /**
     * @todo
     *
     * @param $object
     *
     * @return
     */
    public function afterDelete($object);

    /**
     * Get entity value id
     *
     * @param Varien_Object $entity
     */
    public function getEntityValueId($entity);

    /**
     * Set entity value id
     *
     * @param Varien_Object $entity
     * @param int $valueId
     */
    public function setEntityValueId($entity, $valueId);
}
