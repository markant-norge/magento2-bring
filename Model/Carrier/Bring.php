<?php
namespace Markant\Bring\Model\Carrier;

use GuzzleHttp\Client;

use GuzzleHttp\Exception\RequestException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Markant\Bring\Model\Config\Source\BringMethod;
use Markant\Bring\Model\Tracking\Tracking;
use Magento\Shipping\Helper\Carrier as CarrierHelper;

/**
 * Class Bring
 *
 * http://developer.bring.com/api/shipping-guide/#get-shipment-prices
 *
 * @package Markant\Bring\Model\Carrier
 */
class Bring extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'bring';

    protected $_isFixed = false;



    public function isTrackingAvailable()
    {
        return true;
    }

    const XML_PATH = 'carriers/bring/';


    const BRING_ENDPOINT = 'https://api.bring.com/shippingguide/products/price.json';

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
        array $data = []
    ) {
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
                                            $tracking->setCarrierTitle($this->getConfigData('title'));

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
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();



        $r = [
            'from' => null,
            'to' => null,
            'fromCountry' => 'NO',
            'toCountry' => 'NO',
            'edi' => $this->getConfig('edi') ? 'true' : 'false',
            'postingAtPostOffice' => $this->getConfig('posting_at_post_office') ? 'true' : 'false',
            'language' => 'no',
            'additional' => explode(',', $this->getConfig('additional_services'))
        ];

        if ($request->getOrigPostcode()) {
            $r['from'] = $request->getOrigPostcode();
        } else {
            $r['from'] = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }
        if ($request->getOrigCountryId()) {
            $r['fromCountry'] = $request->getOrigCountryId();
        }


        if ($request->getDestCountryId()) {
            $r['toCountry'] = $request->getDestCountryId();
        }

        if ($request->getDestPostcode()) {
            $r['to'] = $request->getDestPostcode();
        } else {
            $r['to'] = $r['from']; // Just fallback to where it is from.. just to show some prices.
        }

        $weightInG = $request->getPackageWeight() * 1000;
        $r['weightInGrams'] = $weightInG;



        if (!$r['from'] || !$r['to']) {
            return $result;
        }


        try {
            $bring = $this->request(['query' => $r]);

            if ($bring->getStatusCode() === 200) {

                $json = json_decode($bring->getBody(), true);

                if (isset($json['Product'])) {

                    $products = BringMethod::products();
                    foreach ($json['Product'] as $bringAlternative) {
                        if ($this->isBringMethodEnabled($bringAlternative['ProductId'])) {

                            $productLabel = isset($products[$bringAlternative['ProductId']]) ? $products[$bringAlternative['ProductId']] : null;

                            if ($productLabel) {

                                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
                                $method = $this->_rateMethodFactory->create();


                                /*you can fetch shipping price from different sources over some APIs, we used price from config.xml - xml node price*/
                                $amount = $bringAlternative['Price']['PackagePriceWithoutAdditionalServices']['AmountWithVAT'];


                                $method->setCarrier($this->getCarrierCode());
                                $method->setCarrierTitle($this->getConfigData('title'));
                                $method->setMethod($bringAlternative['ProductId']);
                                $method->setMethodTitle($productLabel);

                                $shippingPrice = $this->getFinalPriceWithHandlingFee($amount);

                                $method->setPrice(ceil($shippingPrice));
                                $method->setCost($shippingPrice);
                                $result->append($method);
                            }
                        }
                    }

                }
            } else {
                $error = $this->_trackErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage("test");
                $result->append($error);
            }
        } catch (RequestException $e) {
            $error = $this->_trackErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage("test");
            $result->append($error);
        }




        return $result;
    }

    public function isBringMethodEnabled ($method) {
        $methods = $this->getConfig('enabled_methods');
        if (!$methods) {
            $methods = array_keys(BringMethod::products()); // enable all.
        } else {
            $methods = explode(",", $methods);
        }
        return in_array($method, $methods);
    }


    private function getTrackingEndpoint () {
        return $this->getConfig('enable_mybring') ? self::MYBRING_TRACKING_ENDPOINT : self::BRING_TRACKING_ENDPOINT;
    }


    private function request (array $options) {
        $client = new Client();

        $options = array_merge($options, [
            'headers' => [
                'X-Bring-Client-URL' => $this->getConfig('bring_client_url'),
                'Accept'     => 'application/json'
            ]
        ]);

        return $client->request("get", self::BRING_ENDPOINT, $options);
    }


    private function trackRequest (array $options) {
        $client = new Client();

        $options = array_merge($options, [
            'headers' => [
                'X-Bring-Client-URL' => $this->getConfig('bring_client_url'),
                'Accept'     => 'application/json'
            ]
        ]);
        if ($this->getConfig('enable_mybring')) {
            $options['headers']['X-MyBring-API-Uid'] = $this->getConfig('mybring_client_uid');;
            $options['headers']['X-MyBring-API-Key'] = $this->getConfig('mybring_api_key');
        }

        return $client->request("get", $this->getTrackingEndpoint(), $options);
    }
}