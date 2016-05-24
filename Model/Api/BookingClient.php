<?php
namespace Markant\Bring\Model\Api;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Markant\Bring\Model\Api\Bring\BookingRequest;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/23/16 3:05 PM
 */
class BookingClient
{


    const BRING_CUSTOMERS_API = 'https://api.bring.com/booking/api/customers.json';

    const BRING_BOOKING_API = 'https://api.bring.com/booking/api/booking';

    private $clientId;

    private $apiKey;

    private $clientUrl;

    private $_customers = array();



    public function __construct($clientId, $apiKey, $clientUrl)
    {
        $this->clientId = $clientId;
        $this->apiKey = $apiKey;
        $this->clientUrl = $clientUrl;

        if (!$this->clientId) {
            throw new \Exception("Mybring login ID must not be empty.");
        }
        if (!$this->apiKey) {
            throw new \Exception("Mybring login API KEY must not be empty.");
        }
        if (!$this->clientUrl) {
            throw new \Exception("Bring Client URL must not be empty.");
        }
    }

    public function customersToOptionArray () {
        $option = [];
        foreach ($this->getCustomers() as $customer) {
            $option[] = ['value' => $customer['customerNumber'], 'label' => $customer['name']];
        }
        return $option;
    }

    public function getCustomers () {
        if ($this->_customers) return $this->_customers;

        $request = $this->request('get', self::BRING_CUSTOMERS_API);
        if ($request->getStatusCode() == 200) {
            $json = json_decode($request->getBody(), true);
            $this->_customers = $json['customers'];
            return $json['customers'];
        }
    }

    public function bookShipment (
        BookingRequest $req
    ) {
        $data = $req->toArray();

        $options = [
            'json' => $data
        ];
        $request = $this->request('post', self::BRING_BOOKING_API, $options);
        if ($request->getStatusCode() == 200) {
            $json = json_decode($request->getBody(), true);
            return $json;
        } else {
            throw new \Exception("Could not get 200 response from bring booking API.");
        }
    }


    private function request ($method, $endpoint, array $options = []) {
        $client = new Client();

        $options = array_merge($options, [
            'headers' => [
                'X-Bring-Client-URL' => $this->clientUrl,
                'Accept'     => 'application/json'
            ]
        ]);
        $options['headers']['X-MyBring-API-Uid'] = $this->clientId;;
        $options['headers']['X-MyBring-API-Key'] = $this->apiKey;

        return $client->request($method, $endpoint, $options);
    }
}