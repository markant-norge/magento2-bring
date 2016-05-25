<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Markant\Bring\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Shipment;
use Peec\Bring\API\Contract\Booking\BookingRequest;

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
        \Markant\Bring\Model\BookingClientServiceFactory $bookingClient
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
                $addresses = $shippingAddress->getStreet();
                if (isset($addresses[0])) {
                    $recipient->setAddressLine($addresses[0]);
                }
                if (isset($addresses[1])) {
                    $recipient->setAddressLine2($addresses[1]);
                }
                $recipient->setCity($shippingAddress->getCity());
                $recipient->setCountryCode($shippingAddress->getCountryId());
                $recipient->setName($shippingAddress->getName());
                $recipient->setPostalCode($shippingAddress->getPostcode());
                $recipient->setReference($shippingAddress->getCustomerId());



                $sender = new BookingRequest\Consignment\Address();
                $sender->setName($this->getConfig('booking/origin/name'));
                $sender->setAddressLine($this->getConfig('booking/origin/street_line_1'));
                $sender->setAddressLine2($this->getConfig('booking/origin/street_line_2'));
                $sender->setCity($this->getConfig('booking/origin/city'));
                $sender->setCountryCode($this->getConfig('booking/origin/country_id'));
                $sender->setPostalCode($this->getConfig('booking/origin/postcode'));

                $contact = new BookingRequest\Consignment\Contact();
                $contact->setName($this->getConfig('booking/origin/name'));
                $contact->setEmail($this->getConfig('booking/origin/email'));
                $contact->setPhoneNumber($this->getConfig('booking/origin/phone_number'));

                $sender->setContact($contact);

                // Lets validate sender, since these settings must be displayed in a much nicer manner....
                try {
                    $sender->validate();
                } catch (\Peec\Bring\API\Contract\ContractValidationException $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Shipping Origin is required. Configure sender information under Bring -> Booking.'));
                }


                $consignment->setRecipient($recipient);
                $consignment->setSender($sender);




                $message = new BookingRequest();
                $message->addConsignment($consignment);
                $message->setTestIndicator($bringTestMode);


                /** @var \Markant\Bring\Model\BookingClientService $clientFactory */
                $clientFactory =  $this->_bookingClient->create();
                /** @var \Peec\Bring\API\BookingClient $client */
                $client = $clientFactory->getBookingClient();

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



            } else {
                $response = [
                    'error' => true,
                    'message' => __('We can\'t initialize shipment for adding edi.'),
                ];
            }
        } catch (\Peec\Bring\API\Client\BookingClientException $e) {
            $response = [
                'error' => true,
                'message' => __('Bring API Error:') . " {$e->getDetaildMessage()}.",
            ];
        } catch (\Peec\Bring\API\Contract\ContractValidationException $e) {
            $response = [
                'error' => true,
                'message' => __('Configuration error:') . " {$e->getMessage()}.",
            ];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('Cannot book EDI.')  . " {$e->getMessage()}."];
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
