<?php
/**
 * @package            DPD
 * @subpackage       DelisPrintExport
 * @category               Export
 * @author               Michiel Van Gucht
 */
class DPD_DelisPrintExport_Model_Observer
{
    public function addMassAction($observer)
    {    
        $block = $observer->getEvent()->getBlock();
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'dpdorder')
        {
            $block->addItem('delisprintExport5', array(
                'label' => 'Export to DelisPrint 5',
                'url' => Mage::helper('adminhtml')->getUrl('adminhtml/delisprintexport/export/v/5')
            ));
            
            $block->addItem('delisprintExport6', array(
                'label' => 'Export to DelisPrint 6',
                'url' => Mage::helper('adminhtml')->getUrl('adminhtml/delisprintexport/export/v/6')
            ));
        }
    }
}