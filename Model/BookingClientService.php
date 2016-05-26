<?php
namespace Markant\Bring\Model;
use Peec\Bring\API\Client\BookingClient;
use Peec\Bring\API\Client\Credentials;
use Peec\Bring\API\Client\ShippingGuideClient;
use Peec\Bring\API\Client\TrackingClient;


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

}