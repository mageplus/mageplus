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
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter module observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_Model_Observer
{
    /**
     * @var string
     */
    const XML_PATH_NEWSLETTER_SENDING_COUNT_QUEUE = 'newsletter/sending/count_of_queue';

    /**
     * @var string
     */
    const XML_PATH_NEWSLETTER_SENDING_COUNT_SUBSCRIBER = 'newsletter/sending/count_of_subscriptions';

    /**
     * @todo
     *
     * @param $observer
     * @return
     */
    public function subscribeCustomer($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        if (($customer instanceof Mage_Customer_Model_Customer)) {
            Mage::getModel('newsletter/subscriber')->subscribeCustomer($customer);
        }
        return $this;
    }

    /**
     * Customer delete handler
     *
     * @param Varien_Object $observer
     * @return Mage_Newsletter_Model_Observer
     */
    public function customerDeleted($observer)
    {
        $subscriber = Mage::getModel('newsletter/subscriber')
            ->loadByEmail($observer->getEvent()->getCustomer()->getEmail());
        if($subscriber->getId()) {
            $subscriber->delete();
        }
        return $this;
    }

    /**
     * Schedules the sending process of the newsletters
     *
     * @param $schedule
     * @return
     */
    public function scheduledSend($schedule)
    {
        $countOfQueue = (int) Mage::getStoreConfig(self::XML_PATH_NEWSLETTER_SENDING_COUNT_QUEUE);
        $countOfSubscriptions = (int) Mage::getStoreConfig(self::XML_PATH_NEWSLETTER_SENDING_COUNT_SUBSCRIBER);

        $collection = Mage::getModel('newsletter/queue')->getCollection()
            ->setPageSize($countOfQueue)
            ->setCurPage(1)
            ->addOnlyForSendingFilter()
            ->load();

         $collection->walk('sendPerSubscriber', array($countOfSubscriptions));
    }
}
