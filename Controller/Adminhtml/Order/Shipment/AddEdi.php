<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Markant\Bring\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Shipment;

class AddEdi extends \Magento\Backend\App\Action
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
     * Add new tracking number action
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        try {
            $weight = (float)$this->getRequest()->getPost('weight');
            $length = (float)$this->getRequest()->getPost('length');
            $width = (float)$this->getRequest()->getPost('width');
            $height = (float)$this->getRequest()->getPost('height');
            if (empty($weight) || $weight <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a weight.'));
            }
            if (empty($length) || $length <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a length.'));
            }
            if (empty($width) || $width <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a width.'));
            }
            if (empty($height) || $height <= 0) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a height.'));
            }
            $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load();
            if ($shipment) {
                /** @var \Markant\Bring\Model\Order\Shipment\Edi $edi */
                $edi = $this->_objectManager->create(
                    'Markant\Bring\Model\Order\Shipment\Edi'
                );
                $edi = $edi->setWeight(
                    $weight
                )->setLength(
                    $length
                )->setWidth(
                    $width
                )->setHeight(
                    $height
                );
                $this->addEdi($shipment, $edi)->save();

                $this->_view->loadLayout();
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('EDI Bookings'));
                $response = $this->_view->getLayout()->getBlock('bring_edi_orders')->toHtml();
            } else {
                $response = [
                    'error' => true,
                    'message' => __('We can\'t initialize shipment for adding edi.'),
                ];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot book EDI.')];
        }
        if (is_array($response)) {
            $response = $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($response);
            $this->getResponse()->representJson($response);
        } else {
            $this->getResponse()->setBody($response);
        }
    }




    public function addEdi(Shipment $shipment, \Markant\Bring\Model\Order\Shipment\Edi $edi) {

        $edi->setShipment(
            $shipment
        )->setParentId(
            $shipment->getId()
        )->setOrderId(
            $shipment->getOrderId()
        )->setStoreId(
            $shipment->getStoreId()
        );
        return $edi;
    }
}
