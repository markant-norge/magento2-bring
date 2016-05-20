<?php
namespace Markant\Bring\Model\Config\Source;


/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/18/16 1:34 PM
 */

/**
 * @codeCoverageIgnore
 */
class BringMethod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $_options;


    const PRODUCT_SERVICEPAKKE = 'SERVICEPAKKE';
    const PRODUCT_PA_DOREN = 'PA_DOREN';
    const PRODUCT_BPAKKE_DOR_DOR = 'BPAKKE_DOR-DOR';
    const PRODUCT_EKSPRESS09 = 'EKSPRESS09';
    const PRODUCT_MINIPAKKE = 'MINIPAKKE';
    const PRODUCT_A_POST = 'A-POST';
    const PRODUCT_B_POST = 'B-POST';
    const PRODUCT_SMAAPAKKER_A_POST = 'SMAAPAKKER_A-POST';
    const PRODUCT_SMAAPAKKER_B_POST = 'SMAAPAKKER_B-POST';

    const PRODUCT_EXPRESS_NORDIC_SAME_DAY = 'EXPRESS_NORDIC_SAME_DAY';
    const PRODUCT_EXPRESS_INTERNATIONAL_0900 = 'EXPRESS_INTERNATIONAL_0900';
    const PRODUCT_EXPRESS_INTERNATIONAL_1200 = 'EXPRESS_INTERNATIONAL_1200';
    const PRODUCT_EXPRESS_INTERNATIONAL = 'EXPRESS_INTERNATIONAL';
    const PRODUCT_EXPRESS_ECONOMY = 'EXPRESS_ECONOMY';
    const PRODUCT_CARGO_GROUPAGE = 'CARGO_GROUPAGE';
    const PRODUCT_BUSINESS_PARCEL = 'BUSINESS_PARCEL';
    const PRODUCT_PICKUP_PARCEL = 'PICKUP_PARCEL';
    const PRODUCT_COURIER_VIP = 'COURIER_VIP';
    const PRODUCT_COURIER_1H = 'COURIER_1H';
    const PRODUCT_COURIER_2H = 'COURIER_2H';
    const PRODUCT_COURIER_4H = 'COURIER_4H';
    const PRODUCT_COURIER_6H = 'COURIER_6H';
    const PRODUCT_OIL_EXPRESS = 'OIL_EXPRESS';



    static public function products () {
        return [
            self::PRODUCT_SERVICEPAKKE => 'Servicepakke',
            self::PRODUCT_PA_DOREN => 'På døren',
            self::PRODUCT_BPAKKE_DOR_DOR => 'Dør til dør',
            self::PRODUCT_EKSPRESS09 => 'Bedriftspakke Ekspress-Over natten 09',
            self::PRODUCT_MINIPAKKE => 'Minipakken',
            self::PRODUCT_A_POST => 'A-Prioritert',
            self::PRODUCT_B_POST => 'B-Økonomi',
            self::PRODUCT_SMAAPAKKER_A_POST => 'Småpakke A-Post',
            self::PRODUCT_SMAAPAKKER_B_POST => 'Småpakke B-Økonomi',

            self::PRODUCT_EXPRESS_NORDIC_SAME_DAY => 'Express Nordic Same Day',
            self::PRODUCT_EXPRESS_INTERNATIONAL_0900 => 'Express International 09:00',
            self::PRODUCT_EXPRESS_INTERNATIONAL_1200 => 'Express International 12:00',
            self::PRODUCT_EXPRESS_INTERNATIONAL => 'Express International',
            self::PRODUCT_EXPRESS_ECONOMY => 'Express Economy',
            self::PRODUCT_CARGO_GROUPAGE => 'Cargo',

            self::PRODUCT_BUSINESS_PARCEL => 'Business Parcel',
            self::PRODUCT_PICKUP_PARCEL => 'PickUp Parcel',
            self::PRODUCT_COURIER_VIP => 'Bud VIP',
            self::PRODUCT_COURIER_1H => 'Bud 1 time',
            self::PRODUCT_COURIER_2H => 'Bud 2 timer',
            self::PRODUCT_COURIER_4H => 'Bud 4 timer',
            self::PRODUCT_COURIER_6H => 'Bud 6 timer',
            self::PRODUCT_OIL_EXPRESS => 'Oil Express'

        ];
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
            foreach (self::products() as $k => $v) {
                $this->_options[] = array(
                    'value' => $k,
                    'label' => $v
                );
            }
        }

        $options = $this->_options;
        array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);

        return $options;
    }
}
