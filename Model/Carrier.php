<?php
namespace Markant\Bring\Model;

use GuzzleHttp\Client;

use GuzzleHttp\Exception\RequestException;
use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Markant\Bring\Block\System\Config\Form\Field\ComparisonType;
use Markant\Bring\Block\System\Config\Form\Field\RuleType;
use Markant\Bring\Model\BookingClientService\AdvancedPackageManager;
use Markant\Bring\Model\BookingClientService\Package;
use Markant\Bring\Model\Config\Source\BringMethod;
use Magento\Shipping\Helper\Carrier as CarrierHelper;
use Markantnorge\Bring\API\Client\ShippingGuideClientException;
use Markantnorge\Bring\API\Contract\ContractValidationException;
use Markantnorge\Bring\API\Contract\ShippingGuide\PriceRequest;
use Markantnorge\Bring\API\Contract\Tracking\TrackingRequest;

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

    const XML_GLOBAL_PATH = 'carriers/bring/';
    const XML_PATH = 'carriers/bring/calculation/';



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
        return BringMethod::products();
    }


    /**
     * @return bool true
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * Get tracking information
     *
     * @param string $tracking
     * @return string|false
     * @api
     */
    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            $trackings = $result->getAllTrackings();
            if ($trackings) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }
        return $this->_bringTracking($trackings);
    }


    /**
     * @param array $trackings
     * @return \Magento\Shipping\Model\Tracking\Result
     */
    public function _bringTracking(array $trackings)
    {
        $result = $this->_trackFactory->create();

        /** @var \Markant\Bring\Model\BookingClientService $clientFactory */
        $clientFactory =  $this->_bookingClient->create();
        /** @var \Markantnorge\Bring\API\Client\TrackingClient $client */
        $client = $clientFactory->getTrackingClient();

        foreach ($trackings as $trackingnumber) {


            $request = new TrackingRequest();
            $request->setQuery($trackingnumber);
            $request->setLanguage(\Markantnorge\Bring\API\Data\BringData::LANG_NORWEGIAN);


            try {

                $trackingInfo = $client->getTracking($request);

                foreach ($trackingInfo['consignmentSet'] as $consignmentSet) {
                    // There was an error in this consignment set.
                    if (isset($consignmentSet['error'])) {
                        $error = $this->_trackErrorFactory->create();
                        $error->setCarrier($this->_code);
                        $error->setCarrierTitle($this->getConfigData('title'));
                        $error->setTracking($trackingnumber);
                        $error->setErrorMessage(implode(', ', $consignmentSet['error']));
                        $result->append($error);
                    } else {
                        foreach ($consignmentSet['packageSet'] as $packageSet) {
                            if (isset($packageSet['eventSet'])) {
                                foreach ($packageSet['eventSet'] as $eventSet) {
                                    /** @var \Magento\Shipping\Model\Tracking\Result $tracking */
                                    $tracking = $this->_trackStatusFactory->create();
                                    $tracking->setCarrier($this->_code);
                                    $tracking->setCarrierTitle($this->getConfigData('title'));
                                    $tracking->setTracking($trackingnumber);
                                    $status = $eventSet['description'] ? $eventSet['description'] : $eventSet['status'];
                                    $summary = "{$status} - {$eventSet['displayDate']} {$eventSet['displayTime']}";
                                    $tracking->setTrackSummary($summary);
                                    $result->append($tracking);
                                }
                            }
                        }
                    }
                }

            } catch (\Exception $e) {
                $error = $this->_trackErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($trackingnumber);
                $error->setErrorMessage($e->getMessage());
                $result->append($error);
            }
        }

        return $result;
    }

    public function hydrateRequestData() {
        /** @var RateRequest $request */
        $request = $this->_request;
        $r = [
            'from' => $request->getOrigPostcode(),
            'fromCountry' => $request->getOrigCountryId(),
            'to' => null,
            'toCountry' => null,
            'weightInGram' => null,
            'cart_total' => $request->getOrderSubtotal()

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
            $r['toCountry'] = $r['fromCountry'];
        }


        if ($request->getDestPostcode()) {
            $r['to'] = $request->getDestPostcode();
        }

        
        // Fallback to origin addresses:
        
        // Bring ship origin setting.
        if (!$r['to']) {
            $r['to'] = $this->getStoreConfig('carriers/bring/booking/origin/postcode', $request);
        }
        // Fallback to ship origin settings.
        if (!$r['to']) {
            $r['to'] = $this->getStoreConfig(\Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP, $request);
        }

        return $r;
    }
    
    private function generateOfflineBringShippingMethods (array $data) {
        $methods = [];
        
        $custom_prices = $this->getConfig('custom_method_prices');
        $custom_prices = \Markant\Bring\Helper\Data::unserialize($custom_prices, []);
    
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

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
        /** @var \Markant\Bring\Model\BookingClientService $clientFactory */
        $clientFactory =  $this->_bookingClient->create();

        $containers = $clientFactory->getShippingContainers($request->getAllItems());

        $data = $this->hydrateRequestData();
        // Weight in gram of all packages.
        $data['weightInGram'] = 0;
        foreach ($containers as $container) {
            $data['weightInGram'] += $container->getWeight() * 1000;
        }


        $preFabricatedMethods = $this->generateOfflineBringShippingMethods($data);
        $preFabricatedOverrides = array_keys($preFabricatedMethods);




        // Require post codes from / to to use api ...
        if ($data['to'] && $data['from']) {

            /** @var \Markantnorge\Bring\API\Client\ShippingGuideClient $client */
            $client = $clientFactory->getShippingGuideClient();

            /** @var Package $container */
            foreach ($containers as $container) {
                $priceRequest = new PriceRequest();
                $priceRequest
                    ->setWeightInGrams($container->getWeight() * 1000)
                    ->setEdi($this->getConfig('edi'))
                    ->setFromCountry(strtoupper($data['fromCountry']))
                    ->setFrom($data['from'])
                    ->setToCountry(strtoupper($data['toCountry']))
                    ->setTo($data['to'])
                    ->setPostingAtPostOffice($this->getConfig('posting_at_post_office'))
                    ->setLanguage('no');

                $priceRequest->setLength($container->getLength());
                $priceRequest->setWidth($container->getWidth());
                $priceRequest->setHeight($container->getHeight());


                foreach (explode(',', $this->getConfig('additional_services')) as $service) {
                    $priceRequest->addAdditional($service);
                }
                foreach ($this->getBringEnabledProducts($data) as $product) {
                    $priceRequest->addProduct($product);
                }


                try {



                    $json = $client->getPrices($priceRequest);


                    if (isset($json['consignments'][0]['products'])) {

                        $bringProducts = $json['consignments'][0]['products'];

                        // Single result.... CAN ACTUALLY ENCOUNTER... WIERD THINGS...
                        if (is_array($bringProducts) && isset($bringProducts['ProductId'])) {
                            $bringProducts = array ( $bringProducts ); // Convert it to array...
                        }

                        foreach ($bringProducts as $bringAlternative) {
                            if (isset($bringAlternative['id'])) {  // Should always be isset...
                                $shipping_method = $bringAlternative['id'];
                                if ($this->isBringMethodEnabled($data, $shipping_method)) {
                                    if (isset($bringAlternative['price'])) {
                                        /*you can fetch shipping price from different sources over some APIs, we used price from config.xml - xml node price*/
                                        $AmountWithVAT = $bringAlternative['price']['listPrice']['priceWithAdditionalServices']['amountWithVAT'];
                                        $shippingPrice = $this->getFinalPriceWithHandlingFee($AmountWithVAT);

                                        // Support coupons codes giving free shipping.. If coupons is added that gives free shipping - price is free...
                                        $shippingPrice = ceil($shippingPrice);

                                        $expectedDays = isset($bringAlternative['expectedDelivery']) ? $bringAlternative['expectedDelivery']['workingDays'] : null;

                                        if (!isset($preFabricatedMethods[$shipping_method])) {
                                            $preFabricatedMethods[$shipping_method] = array();
                                        }
                                        $preFabricatedMethods[$shipping_method]['expected_days'] = $expectedDays;
                                        // Do not override prefabricated shipping method prices..
                                        if (!in_array($shipping_method, $preFabricatedOverrides)) {
                                            $preFabricatedMethods[$shipping_method]['price'] = $shippingPrice;
                                            $preFabricatedMethods[$shipping_method]['cost'] = $shippingPrice;
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (ShippingGuideClientException $e) {
                    /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
                    $error = $this->_rateErrorFactory->create();
                    $error->setCarrier(self::CARRIER_CODE);
                    $error->setCarrierTitle($this->getConfigData('title'));
                    /** @var \GuzzleHttp\Exception\RequestException $requestException */
                    $requestException = $e->getPrevious();
                    $error->setErrorMessage($requestException->getResponse()->getBody());
                    $result->append($error);
                    return $result;
                } catch (ContractValidationException $e) {
                    $error = $this->_rateErrorFactory->create();
                    $error->setCarrier(self::CARRIER_CODE);
                    $error->setCarrierTitle($this->getConfigData('title'));
                    $error->setErrorMessage($e->getMessage());
                    $result->append($error);
                    return $result;
                }
            }

        }

        $products = BringMethod::products();



        $free_shipping_method_enabled_discount_amount = floatval($this->getConfigData('free_shipping_method_enabled_discount_amount'));
        $affectedFreeShippingMethods = explode(',', $this->getConfigData('affected_free_shipping_methods'));
        $shouldMaybeHaveFreeShipping = $request->getFreeShipping();
        $haveABringMethodThatIsFree = false;
        foreach ($preFabricatedMethods as $shipping_method => $info) {
            if (in_array($shipping_method, $affectedFreeShippingMethods) && $shouldMaybeHaveFreeShipping) {
                $haveABringMethodThatIsFree = true;
                break;
            }

        }


        uasort($preFabricatedMethods, function ($a, $b) {
            return $a['price'] - $b['price'];
        });



        foreach ($preFabricatedMethods as $shipping_method => $info) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfigData('title'));
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


            //
            // Support free shipping from request ( can e.g. be a coupon code that was activated that gives free shipping! ).
            //
            $finalPrice = $info['price'];
            $finalCost = $info['cost'];
            if (in_array($shipping_method, $affectedFreeShippingMethods) && $shouldMaybeHaveFreeShipping) {
                $finalPrice = '0.00';
                $finalCost = '0.00';
            } else if ($haveABringMethodThatIsFree && $finalPrice >= $free_shipping_method_enabled_discount_amount && $free_shipping_method_enabled_discount_amount > 0) {
                $finalPrice = $finalPrice - $free_shipping_method_enabled_discount_amount;
            }





            $method->setPrice($finalPrice);
            $method->setCost($finalCost);
            $result->append($method);
        }

        return $result;
    }


    public function getBringEnabledProducts (array $hydratedRequestData) {
        $methods = $this->getConfigData('enabled_methods');
        $rules = $this->getConfigData('bring_product_rules');
        if (!$methods) {
            $methods = array_keys(BringMethod::products()); // enable all.
        } else {
            $methods = explode(",", $methods);
        }

        $ruleAggregates = [];
        if ($rules) {
            $rules = \Markant\Bring\Helper\Data::unserialize($rules);
            if ($rules) {
                foreach ($rules as $rule) {
                    if (in_array($rule['bring_product'], $methods)) {

                        $logicalResult = false;
                        $valueToTest = null;
                        switch ($rule['rule']) {
                            case RuleType::CART_WEIGHT:
                                $valueToTest = $hydratedRequestData['weightInGram'] / 1000;
                                break;
                            case RuleType::CART_TOTAL:
                                $valueToTest = $hydratedRequestData['cart_total'];
                                break;
                            default:
                                throw new \Exception("No such bring rule type handler: '{$rule['rule']}'' in getBringEnabledProducts");
                        }
                        $valueToTestAgainst = (float)$rule['value'];
                        switch($rule['comparison']) {
                            case ComparisonType::GT:
                                $logicalResult = $valueToTest > $valueToTestAgainst;
                                break;
                            case ComparisonType::LT:
                                $logicalResult = $valueToTest < $valueToTestAgainst;
                                break;
                            case ComparisonType::LTE:
                                $logicalResult = $valueToTest <= $valueToTestAgainst;
                                break;
                            case ComparisonType::GTE:
                                $logicalResult = $valueToTest >= $valueToTestAgainst;
                                break;
                            default:
                                throw new \Exception("No such bring comparison type handler: '{$rule['comparison']}'' in getBringEnabledProducts");
                                break;
                        }
                        $do = isset($ruleAggregates[$rule['bring_product']]) ? $ruleAggregates[$rule['bring_product']] : true;
                        $ruleAggregates[$rule['bring_product']] = $do && $logicalResult;
                    }
                }
            }
        }

        foreach ($ruleAggregates as $productkey => $keep) {
            if ($keep === false) {
                if(($key = array_search($productkey, $methods)) !== false) {
                    unset($methods[$key]);
                }
            }
        }

        return $methods;
    }


    public function isBringMethodEnabled (array $hydratedRequestData, $method) {
        $methods = $this->getBringEnabledProducts($hydratedRequestData);
        return in_array($method, $methods);
    }


    public function getStoreConfig($id, RateRequest $request) {
        return $this->_scopeConfig->getValue(
            $id,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $request->getStoreId()
        );
    }

}
