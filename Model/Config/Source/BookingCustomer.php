<?php

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/23/16 4:06 PM
 */

namespace Markant\Bring\Model\Config\Source;


use Markant\Bring\Model\Api\BookingClient;

class BookingCustomer implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    protected $_scopeConfig;

    protected $_bookingClient;

    public function __construct(
        \Markant\Bring\Model\Api\BookingClientFactory $bookingClient,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_bookingClient = $bookingClient;
    }

    /**
     * Return options array
     *
     * @param boolean $isMultiselect
     * @param string|array $foregroundCountries
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            try {
                $client = $this->_bookingClient->create();
                /*$client = new BookingClient(
                    $this->_scopeConfig->getValue('carriers/bring/global/bring_client_url'),
                    $this->_scopeConfig->getValue('carriers/bring/global/mybring_client_uid'),
                    $this->_scopeConfig->getValue('carriers/bring/global/mybring_api_key')
                );*/
                $this->_options = $client->customersToOptionArray();
            } catch (\Exception $e) {
                $this->_options = [['value' => '', 'label' => 'ERROR: '.$e->getMessage()]];
            }
        }

        $options = $this->_options;

        return $options;
    }
}
