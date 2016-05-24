<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Markant\Bring\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Shipment;
use Markant\Bring\Model\Api\Bring\BookingRequest;

class AddEdi extends \Magento\Backend\App\Action
{

    const XML_PATH = 'carriers/bring/';



    /**
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;

    protected $_scopeConfig;

    protected $_bookingClient;

    /**
     * @param Action\Context $context
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
     */
    public function __construct(
        Action\Context $context,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Markant\Bring\Model\Api\BookingClientFactory $bookingClient
    ) {
        $this->_bookingClient = $bookingClient;
        $this->shipmentLoader = $shipmentLoader;
        $this->_scopeConfig = $scopeConfig;
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

            $shippingDateTime = $this->getRequest()->getPost('shipping_date_time');
            $shippingDateTimeObj = \DateTime::createFromFormat('Y-m-d H:i', $shippingDateTime);




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

            if ($shippingDateTimeObj === false) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Date format of Shipping Date must be "YYYY-MM-DD HH:SS".'));
            }


            // Find the shipment!
            $this->shipmentLoader->setOrderId($this->getRequest()->getParam('order_id'));
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($this->getRequest()->getParam('shipment'));
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load();


            if ($shipment) {
                $bringCustomerNumber = $this->getConfig('booking/global/default_customer');
                $bringCustomerNumber = 'testing321';
                $bringTestMode = (bool)$this->getConfig('booking/global/test');
                $bringProductId = $this->getRequest()->getPost('product');


                $shippingAddress = $shipment->getShippingAddress();



                //
                // Build up request to send to bring.
                //

                $consignmentPackage = new BookingRequest\Consignment\Package();
                $consignmentPackage->setWeightInKg($weight);
                $consignmentPackage->setDimensionHeightInCm($height);
                $consignmentPackage->setDimensionLengthInCm($length);
                $consignmentPackage->setDimensionWidthInCm($width);


                $bringProduct = new BookingRequest\Consignment\Product();
                $bringProduct->setId($bringProductId);
                $bringProduct->setCustomerNumber($bringCustomerNumber);

                $consignment = new BookingRequest\Consignment();
                $consignment->addPackage($consignmentPackage);
                $consignment->setProduct($bringProduct);
                $consignment->setShippingDateTime($shippingDateTimeObj);


                $recipient = new BookingRequest\Consignment\Address();
                $recipient->setAddressLine($shippingAddress->getStreetLine(0));
                $recipient->setAddressLine2($shippingAddress->getStreetLine(1));
                $recipient->setCity($shippingAddress->getCity());
                $recipient->setCountryCode($shippingAddress->getCountryId());
                $recipient->setName($shippingAddress->getName());
                $recipient->setPostalCode($shippingAddress->getPostcode());
                $recipient->setReference($shippingAddress->getCustomerId());


                $sender = new BookingRequest\Consignment\Address();
                $sender->setAddressLine($this->_scopeConfig->getValue('shipping/origin/street_line1'));
                $sender->setAddressLine2($this->_scopeConfig->getValue('shipping/origin/street_line2'));
                $sender->setCity($this->_scopeConfig->getValue('shipping/origin/city'));
                $sender->setCountryCode($this->_scopeConfig->getValue('shipping/origin/country_id'));
                $name = $this->_scopeConfig->getValue('general/store/information/name')  ? $this->_scopeConfig->getValue('general/store/information/name') : 'general/store/information/name';
                $sender->setName($name);
                $sender->setPostalCode($this->_scopeConfig->getValue('shipping/origin/postcode'));



                $consignment->setRecipient($recipient);
                $consignment->setSender($sender);




                $message = new BookingRequest();
                $message->addConsignment($consignment);
                $message->setTestIndicator($bringTestMode);



                /** @var \Markant\Bring\Model\Api\BookingClient $client */
                $client = $this->_bookingClient->create();

                try {
                    $client->bookShipment($message);




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

                } catch (\Exception $e) {
                    $response = [
                        'error' => true,
                        'message' => __('Bring error:') . " {$e->getMessage()}.",
                    ];
                }

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

    public function getConfig ($key) {
        return $this->_scopeConfig->getValue(
            self::XML_PATH . $key
        );
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
