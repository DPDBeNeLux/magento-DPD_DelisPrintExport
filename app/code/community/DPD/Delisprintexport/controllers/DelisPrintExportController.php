<?php
/**
 * @package            DPD
 * @subpackage       DelisPrintExport
 * @category               Export
 * @author               Michiel Van Gucht
 */
class DPD_DelisPrintExport_DelisprintexportController extends Mage_Adminhtml_Controller_Action
{
    public function exportAction()
    {
        ini_set('max_execution_time', 120);
        $orderIds = $this->getRequest()->getParam('entity_id');
		$delisprintVersion = $this->getRequest()->getParam('v');
        
		try {
            $path = Mage::getModel('delisprintexport/adminhtml_dpdgrid')->delisprintExportOrders($orderIds, $delisprintVersion);
            
            if (!$path) {
                $message = Mage::helper('dpd')->__('No labels for export found.');
                Mage::getSingleton('core/session')->addError($message);
                $this->_redirect('*/*/index');
            } else {
                $filename = "DPD".date("Ymdhis").".csv";
                $this->_prepareDownloadResponse($filename, $path);
            }
		
        } catch (Exception $e) {
            Mage::helper('dpd')->log($e->getMessage(), Zend_Log::ERR);
            $message = Mage::helper('dpd')->__("The file could not be downloaded, please check your DPD logs.");
            Mage::getSingleton('core/session')->addError($message);
            $this->_redirect('*/*/index');
        }
    }
}