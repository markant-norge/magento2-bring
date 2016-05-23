<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Markant\Bring\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;

class RemoveEdi extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    /**
     * @param Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     */
    public function __construct(
        Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
    ) {
        $this->shipmentLoader = $shipmentLoader;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Sales::shipment');
    }

    /**
     * Remove tracking number from shipment
     *
     * @return void
     */
    public function execute()
    {
        $ediId= $this->getRequest()->getParam('edi_id');
        /** @var \Markant\Bring\Model\Order\Shipment\Edi $edi */
        $edi = $this->_objectManager->create('Markant\Bring\Model\Order\Shipment\Edi')->load($ediId);
        if ($edi->getId()) {
            try {
                $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
                $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
                $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
                $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
                $shipment = $this->shipmentLoader->load();
                if ($shipment) {
                    $edi->delete();

                    $this->_view->loadLayout();
                    $this->_view->getPage()->getConfig()->getTitle()->prepend(__('EDI Bookings'));
                    $response = $this->_view->getLayout()->getBlock('bring_edi_orders')->toHtml();
                } else {
                    $response = [
                        'error' => true,
                        'message' => __('We can\'t initialize shipment for delete edi.'),
                    ];
                }
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We can\'t delete edi.')];
            }
        } else {
            $response = [
                'error' => true,
                'message' => __('We can\'t load edi with retrieving identifier right now.')
            ];
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }
}
