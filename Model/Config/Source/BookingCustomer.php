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
        \Markant\Bring\Model\BookingClientServiceFactory $bookingClient,
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
                /** @var \Markant\Bring\Model\BookingClientService $clientFactory */
                $clientFactory =  $this->_bookingClient->create();
                /** @var \Markantnorge\Bring\API\Client\BookingClient $client */
                $client = $clientFactory->getBookingClient();
                $this->_options = $clientFactory->customersToOptionArray($client);
            } catch (\Exception $e) {
                $this->_options = [['value' => '', 'label' => 'ERROR: '.$e->getMessage()]];
            }
        }

        $options = $this->_options;

        return $options;
    }
}
