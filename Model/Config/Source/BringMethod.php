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
use Peec\Bring\API\Data\BringData;

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




    static public function products () {
        return [
            BringData::PRODUCT_SERVICEPAKKE => 'Servicepakke',
            BringData::PRODUCT_PA_DOREN => 'På døren',
            BringData::PRODUCT_BPAKKE_DOR_DOR => 'Dør til dør',
            BringData::PRODUCT_EKSPRESS09 => 'Bedriftspakke Ekspress-Over natten 09',
            BringData::PRODUCT_MINIPAKKE => 'Minipakken',
            BringData::PRODUCT_A_POST => 'A-Prioritert',
            BringData::PRODUCT_B_POST => 'B-Økonomi',
            BringData::PRODUCT_SMAAPAKKER_A_POST => 'Småpakke A-Post',
            BringData::PRODUCT_SMAAPAKKER_B_POST => 'Småpakke B-Økonomi',

            BringData::PRODUCT_EXPRESS_NORDIC_SAME_DAY => 'Express Nordic Same Day',
            BringData::PRODUCT_EXPRESS_INTERNATIONAL_0900 => 'Express International 09:00',
            BringData::PRODUCT_EXPRESS_INTERNATIONAL_1200 => 'Express International 12:00',
            BringData::PRODUCT_EXPRESS_INTERNATIONAL => 'Express International',
            BringData::PRODUCT_EXPRESS_ECONOMY => 'Express Economy',
            BringData::PRODUCT_CARGO_GROUPAGE => 'Cargo',

            BringData::PRODUCT_BUSINESS_PARCEL => 'Business Parcel',
            BringData::PRODUCT_PICKUP_PARCEL => 'PickUp Parcel',
            BringData::PRODUCT_COURIER_VIP => 'Bud VIP',
            BringData::PRODUCT_COURIER_1H => 'Bud 1 time',
            BringData::PRODUCT_COURIER_2H => 'Bud 2 timer',
            BringData::PRODUCT_COURIER_4H => 'Bud 4 timer',
            BringData::PRODUCT_COURIER_6H => 'Bud 6 timer',
            BringData::PRODUCT_OIL_EXPRESS => 'Oil Express'

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
