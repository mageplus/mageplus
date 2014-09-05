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
 * @package     Mage_Shell
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once 'abstract.php';

/**
 * Magento Indexer Shell Script
 *
 * @category    Mage
 * @package     Mage_Shell
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Shell_Indexer extends Mage_Shell_Abstract
{
    /**
     * Get Indexer instance
     *
     * @return Mage_Index_Model_Indexer
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('index/indexer');
    }

    /**
     * Parse string with indexers and return array of indexer instances
     *
     * @param string $string
     * @return array
     */
    protected function _parseIndexerString($string)
    {
        $processes = array();
        if ($string == 'all') {
            $collection = $this->_getIndexer()->getProcessesCollection();
            foreach ($collection as $process) {
                $processes[] = $process;
            }
        } else if (!empty($string)) {
            $codes = explode(',', $string);
            foreach ($codes as $code) {
                $process = $this->_getIndexer()->getProcessByCode(trim($code));
                if (!$process) {
                    echo 'Warning: Unknown indexer with code ' . trim($code) . "\n";
                } else {
                    $processes[] = $process;
                }
            }
        }
        return $processes;
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('info')) {
            $processes = $this->_parseIndexerString('all');
            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                echo sprintf('%-30s', $process->getIndexerCode());
                echo $process->getIndexer()->getName() . "\n";
            }
        } else if ($this->getArg('status') || $this->getArg('mode')) {
            if ($this->getArg('status')) {
                $processes  = $this->_parseIndexerString($this->getArg('status'));
            } else {
                $processes  = $this->_parseIndexerString($this->getArg('mode'));
            }
            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                $status = 'unknown';
                if ($this->getArg('status')) {
                    switch ($process->getStatus()) {
                        case Mage_Index_Model_Process::STATUS_PENDING:
                            $status = 'Pending';
                            break;
                        case Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX:
                            $status = 'Require Reindex';
                            break;

                        case Mage_Index_Model_Process::STATUS_RUNNING:
                            $status = 'Running';
                            break;

                        default:
                            $status = 'Ready';
                            break;
                    }
                } else {
                    switch ($process->getMode()) {
                        case Mage_Index_Model_Process::MODE_REAL_TIME:
                            $status = 'Update on Save';
                            break;
                        case Mage_Index_Model_Process::MODE_MANUAL:
                            $status = 'Manual Update';
                            break;
                    }
                }
                echo sprintf('%-30s ', $process->getIndexer()->getName() . ':') . $status ."\n";

            }
        } else if ($this->getArg('mode-realtime') || $this->getArg('mode-manual')) {
            if ($this->getArg('mode-realtime')) {
                $mode       = Mage_Index_Model_Process::MODE_REAL_TIME;
                $processes  = $this->_parseIndexerString($this->getArg('mode-realtime'));
            } else {
                $mode       = Mage_Index_Model_Process::MODE_MANUAL;
                $processes  = $this->_parseIndexerString($this->getArg('mode-manual'));
            }
            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                try {
                    $process->setMode($mode)->save();
                    echo $process->getIndexer()->getName() . " index was successfully changed index mode\n";
                } catch (Mage_Core_Exception $e) {
                    echo $e->getMessage() . "\n";
                } catch (Exception $e) {
                    echo $process->getIndexer()->getName() . " index process unknown error:\n";
                    echo $e . "\n";
                }
            }
        } else if ($this->getArg('reindex') || $this->getArg('reindexall')) {
            if ($this->getArg('reindex')) {
                $processes = $this->_parseIndexerString($this->getArg('reindex'));
            } else {
                $processes = $this->_parseIndexerString('all');
            }

            foreach ($processes as $process) {
                /* @var $process Mage_Index_Model_Process */
                try {
                    $process->reindexEverything();
                    echo $process->getIndexer()->getName() . " index was rebuilt successfully\n";
                } catch (Mage_Core_Exception $e) {
                    echo $e->getMessage() . "\n";
                } catch (Exception $e) {
                    echo $process->getIndexer()->getName() . " index process unknown error:\n";
                    echo $e . "\n";
                }
            }

        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f indexer.php -- [options]

  --status <indexer>            Show Indexer(s) Status
  --mode <indexer>              Show Indexer(s) Index Mode
  --mode-realtime <indexer>     Set index mode type "Update on Save"
  --mode-manual <indexer>       Set index mode type "Manual Update"
  --reindex <indexer>           Reindex Data
  info                          Show allowed indexers
  reindexall                    Reindex Data by all indexers
  help                          This help

  <indexer>     Comma separated indexer codes or value "all" for all indexers

USAGE;
    }
}

$shell = new Mage_Shell_Indexer();
$shell->run();
