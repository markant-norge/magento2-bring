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
     * @var array
     */
    private $_combinedRates;

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

    protected $helperData;

    /**
     * Carrier constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param CarrierHelper $carrierHelper
     * @param BookingClientServiceFactory $bookingClient
     * @param \Markant\Bring\Helper\Data $helperData
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
        \Markant\Bring\Helper\Data $helperData,
        array $data = []
    ) {
        $this->_bookingClient = $bookingClient;
        $this->_carrierHelper = $carrierHelper;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_trackFactory = $trackFactory;
        $this->_trackErrorFactory = $trackErrorFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        $this->helperData = $helperData;
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
            'cart_total' => $request->getBaseSubtotalInclTax()
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
            $r['fromCountry'] = 'NO';
        }

        if ($request->getDestCountryId()) {
            $r['toCountry'] = $request->getDestCountryId();
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
        $custom_prices = $this->helperData->unserialize($custom_prices, []);

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
                    'expected_days' => true // Unknown if not API is used..
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
        $free_shipping_method_enabled_discount_amount = floatval($this->getConfigData('free_shipping_method_enabled_discount_amount'));
        $affectedFreeShippingMethods = explode(',', $this->getConfigData('affected_free_shipping_methods'));
        $shouldMaybeHaveFreeShipping = $request->getFreeShipping();
        $customerNumbers = explode(",", $this->getConfig('default_customer'));
        foreach ($customerNumbers as $customerNumber) {
            if (!$this->getConfigFlag('active')) {
                return false;
            }
            // if(strpos($customerNumber, "-")!==false){
            //     $customerNumber= explode("-", $customerNumber)[1];    
            // }
            
            $this->_request = $request;

            /** @var \Magento\Shipping\Model\Rate\Result $result */
            $result = $this->_rateResultFactory->create();
            /** @var \Markant\Bring\Model\BookingClientService $clientFactory */
            $clientFactory = $this->_bookingClient->create();

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
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $orderItems = $request->getAllItems();
                        $p_width = [];
                        $weight = 0;
                        $attr=[];
                        foreach ($orderItems as $item) {
                            $product = $item->getProduct();
                            $product2 = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
                            $p_width = $product2->getData('width');
                        }
                        if(!empty($p_width)){
                            $widthToSend=$p_width;
                        }else{
                            $widthToSend=$container->getWidth();
                        }
                    $priceRequest = new PriceRequest();
                    $priceRequest
                        ->setWeightInGrams($container->getWeight() * 1000)
                        ->setEdi($this->getConfig('edi'))
                        ->setEstimatedDeliveryTime(true)
                        ->setFromCountry(strtoupper($data['fromCountry']))
                        ->setFrom($data['from'])
                        ->setToCountry(strtoupper($data['toCountry']))
                        ->setTo($data['to'])
                        ->setPostingAtPostOffice($this->getConfig('posting_at_post_office'))
                        ->setLanguage('no');

                    $priceRequest->setLength($container->getLength());
                    $priceRequest->setWidth($container->getWidth());
                    $priceRequest->setHeight($container->getHeight());
                    $priceRequest->setCustomerNumber($customerNumber);

                    foreach (explode(',', $this->getConfig('additional_services')) as $service) {
                        $priceRequest->addAdditional($service);
                    }
                    foreach ($this->getBringEnabledProducts($data) as $product) {
                        $priceRequest->addProduct($product);
                    }


                    try {

                        // $json='testing';
                        $json = $client->getPrices($priceRequest);
                        
                        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
                        $rootPath  =  $directory->getRoot();
                        // $filehandle=fopen($rootPath."/magento_testing.txt", 'a');
                        // fwrite($filehandle, date("d M-Y H:m")."\n\r");
                        // fwrite($filehandle, print_r($json,true));
                        // fwrite($filehandle, "****Request Data*****"."\n\r");
                        // fwrite($filehandle, print_r($priceRequest,true));

                        //***************Testing dimentions
                        
                        //---------------------------------

                        // fwrite($filehandle, "****Width*****"."\n\r");
                        // fwrite($filehandle, print_r($container->getWidth(),true));

                        // fclose($filehandle);

                        if (isset($json['consignments'][0]['products'])) {

                            $bringProducts = $json['consignments'][0]['products'];

                            // Single result.... CAN ACTUALLY ENCOUNTER... WIERD THINGS...
                            if (is_array($bringProducts) && isset($bringProducts['ProductId'])) {
                                $bringProducts = array($bringProducts); // Convert it to array...
                            }

                            foreach ($bringProducts as $bringAlternative) {
                                if (isset($bringAlternative['id']) && !isset($bringAlternative['errors'])) {  // Should always be isset...
                                    $shipping_method = $bringAlternative['id'];
                                    if ($this->isBringMethodEnabled($data, $shipping_method)) {
                                        if (isset($bringAlternative['price'])) {
                                            /*you can fetch shipping price from different sources over some APIs, we used price from config.xml - xml node price*/
                                            if(isset($bringAlternative['price']['netPrice']))
                                                $AmountWithVAT = $bringAlternative['price']['netPrice']['priceWithAdditionalServices']['amountWithVAT'];
                                            else
                                                $AmountWithVAT = $bringAlternative['price']['listPrice']['priceWithAdditionalServices']['amountWithVAT'];

                                            $shippingPrice = $this->getFinalPriceWithHandlingFee($AmountWithVAT);

                                            // Support coupons codes giving free shipping.. If coupons is added that gives free shipping - price is free...
                                            $shippingPrice = ceil($shippingPrice);

                                            $expectedDays = isset($bringAlternative['estimatedDeliveryTimes']) ? $bringAlternative['estimatedDeliveryTimes'][0]['formattedExpectedDeliveryDate'] : null;

                                            if (!isset($preFabricatedMethods[$shipping_method])) {
                                                $preFabricatedMethods[$shipping_method] = array();
                                            }
                                            $preFabricatedMethods[$shipping_method]['expected_days'] = $expectedDays;
                                            // Do not override prefabricated shipping method prices..
                                            // $filehandle=fopen($rootPath."/magento_testing.txt", 'a');
                                            // fwrite($filehandle, "***fixed prices"."\n\r");
                                            // fwrite($filehandle, date("d M-Y H:m")."\n\r");
                                            // fwrite($filehandle, print_r($bringProducts,true));
                                            // fclose($filehandle);
                                            
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
            uasort($preFabricatedMethods, function ($a, $b) {
                return $a['price'] - $b['price'];
            });
            $this->updateMethodsAccordingToLowestPrices($preFabricatedMethods);
        }
        $products = BringMethod::products();
        $haveABringMethodThatIsFree = false;
        foreach ($this->_combinedRates as $shipping_method => $info) {
            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->_rateMethodFactory->create();
            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod($shipping_method);
            $productLabel = isset($products[$shipping_method]) ? $products[$shipping_method] : $shipping_method;
            $addi_services=explode(',', $this->getConfig('additional_services'));
            if(($shipping_method=='PAKKE_I_POSTKASSEN') && in_array(1081, $addi_services)){
                //$productLabel .= " ( Pose på døren )";
            }
            if($shipping_method==5800){
                foreach($json['consignments'][0]['products'] as $responseProduct){
                    if($responseProduct['id']==5800){
                        $pickup_location_ids=array();
                        if(isset($responseProduct['estimatedDeliveryTimes'])){
                            foreach($responseProduct['estimatedDeliveryTimes'] as $pickup_location_id){
                                $pickup_location_ids[]=$pickup_location_id['pickupPointName'];
                            }
                            $productLabel .= " ( Pickup: ".implode(",", $pickup_location_ids).")";    
                        }
                        
                    }
                }    
            }

            if($shipping_method==5600){
                foreach($json['consignments'][0]['products'] as $responseProduct){
                    if($responseProduct['id']==5600){
                        $pickup_location_ids=array();
                        if(isset($responseProduct['estimatedDeliveryTimes'])){
                            $startTime=$responseProduct['estimatedDeliveryTimes']['deliveryStartTime'];
                            $endTime=$responseProduct['estimatedDeliveryTimes']['deliveryEndTime'];
                            $productLabel .= " ".$startTime."-".$endTime;    
                        }
                        
                    }
                }    
            }
            
            if ($this->getConfig('show_estimated_delivery') && $info['expected_days']) {
                $days = $info['expected_days'];
                if ($days > 1) {
                    $label = new Phrase('%1 days', array($days));
                } else {
                    $label = new Phrase('%1 day', array($days));
                }
                foreach($json['consignments'][0]['products'] as $responseProduct){
                    if(($responseProduct['id']==$shipping_method) || ($responseProduct['id']==3584 && $shipping_method=='PAKKE_I_POSTKASSEN')){
                        if(isset($responseProduct['estimatedDeliveryTimes'])){
                           $label= $responseProduct['estimatedDeliveryTimes'][0]['formattedExpectedDeliveryDate'];
                           // $label=$responseProduct['id'];
                        }
                    }
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
                $haveABringMethodThatIsFree = true;
            }
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
            $rules = $this->helperData->unserialize($rules);
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

    /**
     * @param $preFabricatedMethods
     */
    public function updateMethodsAccordingToLowestPrices($preFabricatedMethods){
        if(!$this->_combinedRates){
            $this->_combinedRates = $preFabricatedMethods;
        }else{
            foreach ($preFabricatedMethods as $shipping_method => $info){
                if(array_key_exists($shipping_method,$this->_combinedRates) && $this->_combinedRates[$shipping_method]["price"] > $info["price"]){
                    $this->_combinedRates[$shipping_method]["expected_days"] = $info["expected_days"];
                    $this->_combinedRates[$shipping_method]["price"] = $info["price"];
                    $this->_combinedRates[$shipping_method]["cost"] = $info["cost"];
                } elseif (!array_key_exists($shipping_method,$this->_combinedRates)){
                    $this->_combinedRates[$shipping_method] = array("expected_days" => $info["expected_days"],"price" => $info["price"],"cost" => $info["cost"]);
                }
            }
        }
    }
}
