<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Markant\Bring\Controller\Adminhtml\Order\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\Stdlib\DateTime;
use Magento\Sales\Model\Order\Shipment;
use Markant\Bring\Model\Carrier;
use Peec\Bring\API\Client\ShippingGuideClientException;
use Peec\Bring\API\Contract\Booking\BookingRequest;
use Peec\Bring\API\Contract\ContractValidationException;
use Peec\Bring\API\Contract\ShippingGuide\PriceRequest;

class EstimateEdi extends \Magento\Backend\App\Action
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
        // Find the shipment!
        $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
        $shipment = $this->shipmentLoader->load();


        $weight = (float)$this->getRequest()->getParam('weight');
        $length = (float)$this->getRequest()->getParam('length');
        $width = (float)$this->getRequest()->getParam('width');
        $height = (float)$this->getRequest()->getParam('height');
        $product = $this->getRequest()->getParam('product');

        $shippingDateTime = $this->getRequest()->getParam('shipping_date_time');
        $shippingDateTimeObj = \DateTime::createFromFormat('Y-m-d H:i', $shippingDateTime);


        $shippingAddress = $shipment->getShippingAddress();
        /** @var \Markant\Bring\Model\BookingClientService $clientFactory */
        $clientFactory = $this->_bookingClient->create();
        /** @var \Peec\Bring\API\Client\ShippingGuideClient $client */
        $client = $clientFactory->getShippingGuideClient();

        $priceRequest = new PriceRequest();
        $priceRequest
            ->setLength($length)
            ->setWidth($width)
            ->setHeight($height)
            ->setWeightInGrams($weight * 1000)
            ->setEdi(true)
            ->setDate($shippingDateTimeObj)
            ->setTime($shippingDateTimeObj)
            ->setFromCountry($this->getConfig('booking/origin/country_id'))
            ->setFrom($this->getConfig('booking/origin/postcode'))
            ->setToCountry(strtoupper($shippingAddress->getCountryId()))
            ->setTo($shippingAddress->getPostcode())
            ->setPostingAtPostOffice($this->getConfig('posting_at_post_office'))
            ->setLanguage('no')
            ->addProduct($product);

        try {

            $prices = $client->getPrices($priceRequest);

            if (isset($prices['Product'])) {
                $bringAlternative = $prices['Product'];
                $response = ['error' => false, 'message' => $bringAlternative['Price']['PackagePriceWithAdditionalServices'], 'request' => $priceRequest->toArray()];
            } else {
                $response = ['error' => true, 'message' => implode("<br/>", $prices['TraceMessages'])];
            }
        } catch (ContractValidationException $e) {
            $response = ['error' => true, 'message' => $e->getMessage(), 'type' => 'ContractValidationException'];
        } catch (ShippingGuideClientException $e) {
            $response = ['error' => true, 'message' => $e->getMessage(), 'request' => $priceRequest->toArray(), 'type' => 'ShippingGuideClientException'];
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
        }


        $response = $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($response);
        $this->getResponse()->representJson($response);

    }



    public function getConfig ($key) {
        return $this->_scopeConfig->getValue(
            self::XML_PATH . $key
        );
    }


}
