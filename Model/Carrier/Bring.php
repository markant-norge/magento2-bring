<?php
namespace Markant\Bring\Model\Carrier;

use GuzzleHttp\Client;

use GuzzleHttp\Exception\RequestException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Markant\Bring\Model\Config\Source\BringMethod;

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
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_trackErrorFactory = $trackErrorFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function getConfig ($key, $request) {
        return $this->_scopeConfig->getValue(
            self::XML_PATH . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $request->getStoreId()
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
            'edi' => $this->getConfig('edi', $request) ? 'true' : 'false',
            'postingAtPostOffice' => $this->getConfig('posting_at_post_office', $request) ? 'true' : 'false',
            'language' => 'no',
            'additional' => explode(',', $this->getConfig('additional_services', $request))
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
                        if ($this->isBringMethodEnabled($bringAlternative['ProductId'], $request)) {

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
                                $method->setPrice(ceil($amount));
                                $method->setCost($amount);
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

    public function isBringMethodEnabled ($method, $request) {
        $methods = $this->getConfig('enabled_methods', $request);
        if (!$methods) {
            $methods = array_keys(BringMethod::products()); // enable all.
        } else {
            $methods = explode(",", $methods);
        }
        return in_array($method, $methods);
    }


    private function request (array $options) {
        $client = new Client();

        $options = array_merge($options, [
            'headers' => [
                'X-Bring-Client-URL' => 'testing/1.0',
                'Accept'     => 'application/json'
            ]
        ]);

        return $client->request("get", self::BRING_ENDPOINT, $options);
    }

}