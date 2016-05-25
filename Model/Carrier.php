<?php
namespace Markant\Bring\Model;

use GuzzleHttp\Client;

use GuzzleHttp\Exception\RequestException;
use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Markant\Bring\Model\Config\Source\BringMethod;
use Markant\Bring\Model\Tracking\Tracking;
use Magento\Shipping\Helper\Carrier as CarrierHelper;
use Peec\Bring\API\Client\ShippingGuideClientException;
use Peec\Bring\API\Contract\ContractValidationException;
use Peec\Bring\API\Contract\ShippingGuide\PriceRequest;

/**
 * Class Bring
 *
 * http://developer.bring.com/api/shipping-guide/#get-shipment-prices
 *
 * @package Markant\Bring\Model\Carrier
 */
class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{

    const CARRIER_CODE = 'bring';

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    protected $_isFixed = false;



    public function isTrackingAvailable()
    {
        return true;
    }

    const XML_GLOBAL_PATH = 'carriers/bring/global/';
    const XML_PATH = 'carriers/bring/calculation/';


    const BRING_ENDPOINT = 'https://api.bring.com/shippingguide/products/all.json';

    const BRING_TRACKING_ENDPOINT = 'https://tracking.bring.com/tracking.json';
    const MYBRING_TRACKING_ENDPOINT = 'https://www.mybring.com/tracking/api/tracking.json';

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $_rateMethodFactory;

    private $_request;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\ErrorFactory
     */
    protected $_trackErrorFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\ResultFactory
     */
    protected $_trackFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    protected $_trackStatusFactory;

    /**
     * Carrier helper
     *
     * @var \Magento\Shipping\Helper\Carrier
     */
    protected $_carrierHelper;

    protected $_bookingClient;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        CarrierHelper $carrierHelper,
        \Markant\Bring\Model\BookingClientServiceFactory $bookingClient,
        array $data = []
    ) {
        $this->_bookingClient = $bookingClient;
        $this->_carrierHelper = $carrierHelper;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_trackFactory = $trackFactory;
        $this->_trackErrorFactory = $trackErrorFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function getConfig ($key) {
        return $this->_scopeConfig->getValue(
            self::XML_PATH . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getData('store')
        );
    }
    public function getGlobalConfig ($key) {
        return $this->_scopeConfig->getValue(
            self::XML_GLOBAL_PATH . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getData('store')
        );
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return self::products();
    }



    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTrackingInfo($trackings)
    {
        $result = $this->_trackFactory->create();;

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        foreach ($trackings as $trackingnumber) {

            $r = [
                'q' => $trackingnumber
            ];

            try {
                $bring = $this->trackRequest(['query' => $r]);

                if ($bring->getStatusCode() === 200) {

                    $json = json_decode($bring->getBody(), true);

                    if (isset($json['consignmentSet'])) {
                        foreach ($json['consignmentSet'] as $consignmentSet) {
                            if (isset($consignmentSet['packageSet'])) {
                                foreach ($consignmentSet['packageSet'] as $packageSet) {
                                    if (isset($packageSet['eventSet'])) {
                                        foreach ($packageSet['eventSet'] as $eventSet) {
                                            $tracking = $this->_trackStatusFactory->create();
                                            $tracking->setCarrier($this->_code);
                                            $tracking->setCarrierTitle($this->getConfig('title'));

                                            $tracking->setTracking($trackingnumber);
                                            $status = Tracking::humanize($eventSet['status']);
                                            $summary = "$status - {$eventSet['displayDate']} {$eventSet['displayTime']}";
                                            $tracking->setTrackSummary($summary);
                                            $result->append($tracking);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                }
            } catch (RequestException $e) {
            }


        }


        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            $trackings = $result->getAllTrackings();
            if ($trackings) {
                return $trackings[0];
            }
        }
        return false;
    }

    public function hydrateRequestData() {
        /** @var RateRequest $request */
        $request = $this->_request;
        $r = [
            'from' => $request->getOrigPostcode(),
            'fromCountry' => $request->getOrigCountryId(),
            'to' => null,
            'toCountry' => null,
            'weightInGram' => $request->getPackageWeight() * 1000,
            'width' => $request->getPackageWidth(),
            'length' => $request->getPackageHeight(),
            'height' => $request->getPackageDepth()

        ];

        // Bring ship origin setting.
        if (!$r['from']) {
            $r['from'] = $this->getStoreConfig('carriers/bring/booking/origin/postcode', $request);
        }
        // Fallback to ship origin settings.
        if (!$r['from']) {
            $r['from'] = $this->getStoreConfig(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP, $request);
        }


        // Bring ship origin setting.
        if (!$r['fromCountry']) {
            $r['fromCountry'] = $this->getStoreConfig('carriers/bring/booking/origin/country_id', $request);
        }
        // Fallback to ship origin settings.
        if (!$r['fromCountry']) {
            $r['fromCountry'] = $this->getStoreConfig(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID, $request);
        }
        if (!$r['fromCountry']) {
            $r['fromCountry'] = 'no';
        }

        if ($request->getDestCountryId()) {
            $r['toCountry'] = strtolower($request->getDestCountryId());
        }
        if (!$r['toCountry']) {
            $r['toCountry'] = 'no';
        }


        if ($request->getDestPostcode()) {
            $r['to'] = $request->getDestPostcode();
        }

        if (!$r['width']) {
            $r['width'] = $this->getStoreConfig('carriers/bring/booking/package/width', $request);
        }

        if (!$r['length']) {
            $r['length'] = $this->getStoreConfig('carriers/bring/booking/package/length', $request);
        }
        if (!$r['height']) {
            $r['height'] = $this->getStoreConfig('carriers/bring/booking/package/height', $request);
        }

        return $r;
    }

    private function generateOfflineBringShippingMethods (array $data) {
        $methods = [];

        $custom_prices = $this->getConfig('custom_method_prices');
        $custom_prices = $custom_prices ? unserialize($custom_prices) : [];

        foreach ($custom_prices as $item) {
            $add = true;
            if ($item['min_weight']) {
                $add &= $item['min_weight'] <= $data['weightInGram'];
            }
            if ($item['max_weight']) {
                $add &= $item['max_weight'] >= $data['weightInGram'];
            }

            if (isset($item['country']) && $item['country']) {
                $add &= $item['country'] == $data['toCountry'];
            }

            if ($add) {
                $shippingPrice = $this->getFinalPriceWithHandlingFee((float)$item['price']);
                $methods[$item['shipping_method']] = array (
                    'price' => ceil($shippingPrice),
                    'cost' => $shippingPrice,
                    'expected_days' => null // Unknown if not API is used..
                );
            }
        }
        return $methods;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $this->_request = $request;

        $data = $this->hydrateRequestData();

        $preFabricatedMethods = $this->generateOfflineBringShippingMethods($data);
        $preFabricatedOverrides = array_keys($preFabricatedMethods);


        // Require post codes from / to to use api ...
        if ($this->getConfig('activate_api') && $data['to'] && $data['from']) {


            /** @var \Markant\Bring\Model\BookingClientService $clientFactory */
            $clientFactory =  $this->_bookingClient->create();
            /** @var \Peec\Bring\API\Client\ShippingGuideClient $client */
            $client = $clientFactory->getShippingGuideClient();

            $priceRequest = new PriceRequest();
            $priceRequest
                ->setWeightInGrams($data['weightInGram'])
                ->setEdi($this->getConfig('edi'))
                ->setFromCountry(strtoupper($data['fromCountry']))
                ->setFrom($data['from'])
                ->setToCountry(strtoupper($data['toCountry']))
                ->setTo($data['to'])
                ->setPostingAtPostOffice($this->getConfig('posting_at_post_office'))
                ->setLanguage('no');

            if ($this->getConfig('use_packagesize')) {
                $priceRequest->setLength($data['length']);
                $priceRequest->setWidth($data['width']);
                $priceRequest->setHeight($data['height']);
            }
            foreach (explode(',', $this->getConfig('additional_services')) as $service) {
                $priceRequest->addAdditional($service);
            }
            foreach ($this->getBringEnabledProducts() as $product) {
                $priceRequest->addProduct(strtolower($product));
            }


            try {



                $json = $client->getPrices($priceRequest);

                foreach ($json['Product'] as $bringAlternative) {
                    $shipping_method = $bringAlternative['ProductId'];
                    if ($this->isBringMethodEnabled($shipping_method)) {
                        /*you can fetch shipping price from different sources over some APIs, we used price from config.xml - xml node price*/
                        $amount = $bringAlternative['Price']['PackagePriceWithAdditionalServices']['AmountWithVAT'];
                        $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);


                        $expectedDays = isset($bringAlternative['ExpectedDelivery']) ? $bringAlternative['ExpectedDelivery']['WorkingDays'] : null;

                        if (!isset($preFabricatedMethods[$shipping_method])) {
                            $preFabricatedMethods[$shipping_method] = array();
                        }
                        $preFabricatedMethods[$shipping_method]['expected_days'] = $expectedDays;
                        // Do not override prefabricated shipping method prices..
                        if (!in_array($shipping_method, $preFabricatedOverrides)) {
                            $preFabricatedMethods[$shipping_method]['price'] = ceil($shippingPrice);
                            $preFabricatedMethods[$shipping_method]['cost'] = $shippingPrice;
                        }
                    }
                }
            } catch (ShippingGuideClientException $e) {
                // Silent
                throw $e;
            } catch (ContractValidationException $e) {
                // Silent...
                throw $e;
            }
        }

        $products = BringMethod::products();

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();

        foreach ($preFabricatedMethods as $shipping_method => $info) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfig('title'));
            $method->setMethod($shipping_method);
            $productLabel = isset($products[$shipping_method]) ? $products[$shipping_method] : $shipping_method;

            if ($this->getConfig('show_estimated_delivery') && $info['expected_days']) {
                $days = $info['expected_days'];
                if ($days > 1) {
                    $label = new Phrase('%1 days', array($days));
                } else {
                    $label = new Phrase('%1 day', array($days));
                }
                $productLabel .= " ($label)";
            }

            $method->setMethodTitle($productLabel);
            $method->setPrice($info['price']);
            $method->setCost($info['cost']);
            $result->append($method);
        }

        return $result;
    }


    public function getBringEnabledProducts () {
        $methods = $this->getConfig('enabled_methods');
        if (!$methods) {
            $methods = array_keys(BringMethod::products()); // enable all.
        } else {
            $methods = explode(",", $methods);
        }
        return $methods;
    }


    public function isBringMethodEnabled ($method) {
        $methods = $this->getBringEnabledProducts();
        return in_array($method, $methods);
    }


    private function getTrackingEndpoint () {
        return $this->getConfig('enable_mybring') ? self::MYBRING_TRACKING_ENDPOINT : self::BRING_TRACKING_ENDPOINT;
    }


    private function request (array $options) {
        $client = new Client();

        $options = array_merge($options, [
            'headers' => [
                'X-Bring-Client-URL' => $this->getGlobalConfig('bring_client_url'),
                'Accept'     => 'application/json'
            ]
        ]);

        return $client->request("get", self::BRING_ENDPOINT, $options);
    }


    private function trackRequest (array $options) {
        $client = new Client();

        $options = array_merge($options, [
            'headers' => [
                'X-Bring-Client-URL' => $this->getGlobalConfig('bring_client_url'),
                'Accept'     => 'application/json'
            ]
        ]);
        if ($this->getConfig('enable_mybring')) {
            $options['headers']['X-MyBring-API-Uid'] = $this->getGlobalConfig('mybring_client_uid');;
            $options['headers']['X-MyBring-API-Key'] = $this->getGlobalConfig('mybring_api_key');
        }

        return $client->request("get", $this->getTrackingEndpoint(), $options);
    }


    public function getStoreConfig($id, RateRequest $request) {
        return $this->_scopeConfig->getValue(
            $id,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $request->getStoreId()
        );
    }

}