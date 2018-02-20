<?php
namespace Markant\Bring\Model;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Markant\Bring\Model\BookingClientService\AdvancedPackageManager;
use Markant\Bring\Model\BookingClientService\Package;
use Markantnorge\Bring\API\Client\BookingClient;
use Markantnorge\Bring\API\Client\Credentials;
use Markantnorge\Bring\API\Client\ShippingGuideClient;
use Markantnorge\Bring\API\Client\TrackingClient;


/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/23/16 3:05 PM
 */
class BookingClientService
{


    const BRING_CUSTOMERS_API = 'https://api.bring.com/booking/api/customers.json';

    const BRING_BOOKING_API = 'https://api.bring.com/booking/api/booking';

    private $clientId;

    private $apiKey;

    private $clientUrl;

    private $_scopeConfig;


    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
        $this->clientUrl = $this->_scopeConfig->getValue('carriers/bring/bring_client_url');
        $this->clientId = $this->_scopeConfig->getValue('carriers/bring/mybring_client_uid');
        $this->apiKey = $this->_scopeConfig->getValue('carriers/bring/mybring_api_key');

    }

    public function getBookingClient() {
        return new BookingClient(new Credentials($this->clientUrl, $this->clientId, $this->apiKey));
    }

    /**
     * @return ShippingGuideClient
     */
    public function getShippingGuideClient() {
        return new ShippingGuideClient(new Credentials($this->clientUrl, $this->clientId, $this->apiKey));
    }


    /**
     * @return ShippingGuideClient
     */
    public function getTrackingClient() {
        return new TrackingClient(new Credentials($this->clientUrl, $this->clientId, $this->apiKey));
    }


    public function customersToOptionArray (BookingClient $client) {
        $option = [];
        foreach ($client->getCustomers() as $customer) {
            $option[] = ['value' => $customer['customerNumber'], 'label' => $customer['name']];
        }
        return $option;
    }


    /**
     * See carriers/bring/package_management/packages
     * Gets Built in packages that is configured in admin..
     * @return array
     */
    public function getBuiltInPackages () {
        $builtIns = [];

        $builtIn = $this->_scopeConfig->getValue('carriers/bring/package_management/packages');
        if ($builtIn) {
            $builtIn = unserialize($builtIn);
            $builtIns = [];
            foreach ($builtIn as $item) {
                $package = new Package();
                $package->setWidth($item['width']);
                $package->setHeight($item['height']);
                $package->setLength($item['length']);
                $package->setFactorAttributeCode($item['factor_attribute']);
                $builtIns[] = $package;
            }
        }
        return $builtIns;
    }


    public function getShippingContainers (array $items) {

        $packages = [];


        $manager = new AdvancedPackageManager($items);
        $manager->setAttributeHeightCode($this->_scopeConfig->getValue('carriers/bring/package_management/height_attribute'));
        $manager->setAttributeWidthCode($this->_scopeConfig->getValue('carriers/bring/package_management/width_attribute'));
        $manager->setAttributeLengthCode($this->_scopeConfig->getValue('carriers/bring/package_management/length_attribute'));
        $manager->setAttributeShippedIndividuallyCode($this->_scopeConfig->getValue('carriers/bring/package_management/ship_individually_attribute'));

        $manager->setDefaultLength($this->_scopeConfig->getValue('carriers/bring/package_management/standard_package_length'));
        $manager->setDefaultWidth($this->_scopeConfig->getValue('carriers/bring/package_management/standard_package_width'));
        $manager->setDefaultHeight($this->_scopeConfig->getValue('carriers/bring/package_management/standard_package_height'));
        $manager->setDefaultWeight($this->_scopeConfig->getValue('carriers/bring/package_management/default_product_weight'));

        $manager->setBuiltInPackages($this->getBuiltInPackages());
        $packages = $manager->calculate();
        return $packages;
    }

}