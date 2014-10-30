<?php
/**
 * @package            DPD
 * @subpackage       DelisPrintExport
 * @category               Export
 * @author               Michiel Van Gucht
 */
class DPD_DelisPrintExport_Model_Adminhtml_Dpdgrid extends Mage_Core_Model_Abstract
{
	/**
     * Creates an export to delisprint
     *
     * @param array $orderIds
     */
	
	public function delisprintExportOrders($orderIds, $version = '5')
	{
		$csvShipmentsArray = array();
        foreach ($orderIds as $orderId) {
			$order = Mage::getModel('sales/order')->load($orderId);
			$csvString = $this->_getOrderCsvData($order, $version);
			if($csvString != ""){
				$csvShipmentsArray[] = $csvString;
			}
		}
		
		$path = Mage::getBaseDir('var') . DS . 'export' . DS . 'delisprint' . DS; //best would be to add exported path through config
		$name = md5(microtime());
        $file = $path . DS . $name . '.csv';
		while (file_exists($file)) {
            sleep(1);
            $name = md5(microtime());
            $file = $path . DS . $name . '.csv';
        }
		
		$io = new Varien_Io_File();
		$io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
		$io->streamWrite(implode("\r\n", $csvShipmentsArray));
		$io->streamUnlock();
        $io->streamClose();
		
		return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => false // can delete file after use
        );
	}
	
	protected function _getOrderCsvData($order, $version)
    {
		$csvLineArray = array();
		
		$shippingMethod = $order->getShippingMethod();
		
		$cneeAddress;
		
        $shipment = $order->prepareShipment();
        $shipment->register();
        $weight = Mage::helper('dpd')->calculateTotalShippingWeight($shipment);
		
		switch ($shippingMethod){
			case 'dpdclassic_dpdclassic':
				switch ($version){
				    case '5':
						$csvLineArray[] = $weight > 3.00 ? 'NCP, PRO' : 'SCP, PRO';
					    break;
					case '6':
						$csvLineArray[] = $weight > 3.00 ?  'NP, B2C, PAN' : 'SP, B2C, PAN';
					    break;
				}
				
				$cneeAddress = $order->getShippingAddress();
				break;
			case 'dpdparcelshops_dpdparcelshops':
			    switch ($version){
				    case '5':
				        $csvLineArray[] = $weight > 3.00 ? 'NCP, PRO, PS' : 'SCP, PRO, PS';
				        break;
					case '6':
						$csvLineArray[] = $weight > 3.00 ? 'NP, B2C, PAN, PSD' : 'SP, B2C, PAN, PSD';
					    break;
				}
				$cneeAddress = $order->getBillingAddress();
				break;
		}
		
		$csvLineArray[] = $cneeAddress->getFirstname() . " " . $cneeAddress->getLastname();
		$csvLineArray[] = $cneeAddress->getStreet(1) . " " . $cneeAddress->getStreet(2);
		$csvLineArray[] = $cneeAddress->getCountry();
		$csvLineArray[] = $cneeAddress->getPostcode();
		$csvLineArray[] = $cneeAddress->getCity();
		
		$csvLineArray[] = $order->getRealOrderId();
		
		$csvLineArray[] = $weight;
		$csvLineArray[] = '1';
		
        $locale = Mage::app()->getStore($order->getStoreId())->getConfig('general/locale/code');
        $localeCode = explode('_', $locale);
		
		 switch ($version){
		    case '5':
				$csvLineArray[] = 'E';
			    break;
			case '6':
                $csvLineArray[] = '1';
				break;
		}
        $csvLineArray[] = $cneeAddress->getEmail();
        $csvLineArray[] = '904';
        $csvLineArray[] = strtoupper($localeCode[0]);
		
		if($shippingMethod == "dpdparcelshops_dpdparcelshops"){
			$shippingAddress = $order->getShippingAddress();
			
			$csvLineArray[] = $shippingAddress->getLastname();
			$csvLineArray[] = $shippingAddress->getStreet(1) . " " . $shippingAddress->getStreet(2);
			$csvLineArray[] = $shippingAddress->getCountry();
			$csvLineArray[] = $shippingAddress->getPostcode();
			$csvLineArray[] = $shippingAddress->getCity();
			$csvLineArray[] = $order->getDpdParcelshopId();
		} else {
			for($i = 0; $i < 8; $i++)
			{
			    $csvLineArray[] = "";
			}
		}
		
		if(!count($csvLineArray)) {
			return "";
		} else {
			return implode(";", $csvLineArray);
		}
	}

}