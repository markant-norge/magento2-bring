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
use Markantnorge\Bring\API\Data\BringData;

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
            BringData::PRODUCT_SERVICEPAKKE => __('Servicepakke'),
            BringData::PRODUCT_PA_DOREN => __('På døren'),
            BringData::PRODUCT_BPAKKE_DOR_DOR => __('Dør til dør'),
            BringData::PRODUCT_EKSPRESS09 => __('Bedriftspakke Ekspress-Over natten 09'),
            BringData::PRODUCT_MINIPAKKE => __('Minipakken'),
            'MAIL' => __('Brevpost'), // Should be changed to constant, when API SDK is updated
			
            BringData::PRODUCT_A_POST => __('A-Prioritert'),
            BringData::PRODUCT_B_POST => __('B-Økonomi'),
            BringData::PRODUCT_SMAAPAKKER_A_POST => __('Småpakke A-Post'),
            BringData::PRODUCT_SMAAPAKKER_B_POST => __('Småpakke B-Økonomi'),

            BringData::PRODUCT_EXPRESS_NORDIC_SAME_DAY => __('Express Nordic Same Day'),
            BringData::PRODUCT_EXPRESS_INTERNATIONAL_0900 => __('Express International 09:00'),
            BringData::PRODUCT_EXPRESS_INTERNATIONAL_1200 => __('Express International 12:00'),
            BringData::PRODUCT_EXPRESS_INTERNATIONAL => __('Express International'),
            BringData::PRODUCT_EXPRESS_ECONOMY => __('Express Economy'),
            BringData::PRODUCT_CARGO_GROUPAGE => __('Cargo'),

            BringData::PRODUCT_BUSINESS_PARCEL => __('Business Parcel'),
            BringData::PRODUCT_PICKUP_PARCEL => __('PickUp Parcel'),
            BringData::PRODUCT_COURIER_VIP => __('Bud VIP'),
            BringData::PRODUCT_COURIER_1H => __('Bud 1 time'),
            BringData::PRODUCT_COURIER_2H => __('Bud 2 timer'),
            BringData::PRODUCT_COURIER_4H => __('Bud 4 timer'),
            BringData::PRODUCT_COURIER_6H => __('Bud 6 timer'),
            BringData::PRODUCT_OIL_EXPRESS => __('Oil Express')

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
