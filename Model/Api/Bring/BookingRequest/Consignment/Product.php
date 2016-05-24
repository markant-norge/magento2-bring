<?php
namespace Markant\Bring\Model\Api\Bring\BookingRequest\Consignment;
use Markant\Bring\Model\Api\Bring\DataValidationException;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 *
 * - id
 * - customerNumber
 * - services
 * - customsDeclaration
 *
 * @author petterk
 * @date 5/24/16 9:55 AM
 */
class Product extends \Markant\Bring\Model\Api\Bring\ApiEntity
{
    protected $_data = [
        'services' => null,
        'customsDeclaration' => null
    ];

    const ADDITIONAL_SERVICE_CASH_ON_DELIVERY = 'cashOnDelivery';
    const ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION = 'recipientNotification';
    const ADDITIONAL_SERVICE_SOCIAL_CONTROL = 'socialControl';
    const ADDITIONAL_SERVICE_SIMPLE_DELIVERY = 'simpleDelivery';
    const ADDITIONAL_SERVICE_DELIVERY_OPTION = 'deliveryOption';
    const ADDITIONAL_SERVICE_SATURDAY_DELIVERY = 'saturdayDelivery';
    const ADDITIONAL_SERVICE_FLEX_DELIVERY= 'flexDelivery';
    const ADDITIONAL_SERVICE_PHONE_NOTIFICATION= 'phonenotification';
    const ADDITIONAL_SERVICE_DELIVERY_INDOORS= 'deliveryIndoors';


    const PRODUCT_SERVICEPAKKE = 'SERVICEPAKKE';
    const PRODUCT_BPAKKE_DOR_DOR = 'BPAKKE_DOR-DOR';
    const PRODUCT_EKSPRESS09 = 'EKSPRESS09';
    const PRODUCT_PICKUP_PARCEL = 'PICKUP_PARCEL';
    const PRODUCT_PICKUP_PARCEL_BULK = 'PICKUP_PARCEL_BULK';
    const PRODUCT_HOME_DELIVERY_PARCEL = 'HOME_DELIVERY_PARCEL';
    const PRODUCT_BUSINESS_PARCEL = 'BUSINESS_PARCEL';
    const PRODUCT_BUSINESS_PARCEL_BULK = 'BUSINESS_PARCEL_BULK';
    const PRODUCT_EXPRESS_NORDIC_0900_BULK = 'EXPRESS_NORDIC_0900_BULK';
    const PRODUCT_BUSINESS_PALLET = 'BUSINESS_PALLET';
    const PRODUCT_BUSINESS_PARCEL_HALFPALLET = 'BUSINESS_PARCEL_HALFPALLET';
    const PRODUCT_BUSINESS_PARCEL_QUARTERPALLET = 'BUSINESS_PARCEL_QUARTERPALLET';
    const PRODUCT_EXPRESS_NORDIC_0900 = 'EXPRESS_NORDIC_0900';
    const PRODUCT_PA_DOREN = 'PA_DOREN';
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
    const PRODUCT_COURIER_VIP = 'COURIER_VIP';
    const PRODUCT_COURIER_1H = 'COURIER_1H';
    const PRODUCT_COURIER_2H = 'COURIER_2H';
    const PRODUCT_COURIER_4H = 'COURIER_4H';
    const PRODUCT_COURIER_6H = 'COURIER_6H';
    const PRODUCT_OIL_EXPRESS = 'OIL_EXPRESS';

    /**
     * See http://developer.bring.com/api/booking/
     */
    static public function validProducts () {
        return [
            self::PRODUCT_SERVICEPAKKE,
            self::PRODUCT_BPAKKE_DOR_DOR,
            self::PRODUCT_EKSPRESS09,
            self::PRODUCT_PICKUP_PARCEL,
            self::PRODUCT_PICKUP_PARCEL_BULK,
            self::PRODUCT_HOME_DELIVERY_PARCEL,
            self::PRODUCT_BUSINESS_PARCEL,
            self::PRODUCT_BUSINESS_PARCEL_BULK,
            self::PRODUCT_EXPRESS_NORDIC_0900_BULK,
            self::PRODUCT_BUSINESS_PALLET,
            self::PRODUCT_BUSINESS_PARCEL_HALFPALLET,
            self::PRODUCT_BUSINESS_PARCEL_QUARTERPALLET,
            self::PRODUCT_EXPRESS_NORDIC_0900,
            self::PRODUCT_PA_DOREN,
            self::PRODUCT_MINIPAKKE,
            self::PRODUCT_A_POST,
            self::PRODUCT_B_POST,
            self::PRODUCT_PA_DOREN,
            self::PRODUCT_SMAAPAKKER_A_POST,
            self::PRODUCT_SMAAPAKKER_B_POST,
            self::PRODUCT_EXPRESS_NORDIC_SAME_DAY,
            self::PRODUCT_EXPRESS_INTERNATIONAL_0900,
            self::PRODUCT_EXPRESS_INTERNATIONAL_1200,
            self::PRODUCT_EXPRESS_INTERNATIONAL,
            self::PRODUCT_EXPRESS_ECONOMY,
            self::PRODUCT_CARGO_GROUPAGE,
            self::PRODUCT_COURIER_VIP,
            self::PRODUCT_COURIER_1H,
            self::PRODUCT_COURIER_2H,
            self::PRODUCT_COURIER_4H,
            self::PRODUCT_COURIER_6H,
            self::PRODUCT_OIL_EXPRESS

        ];
    }

    /**
     * See http://developer.bring.com/api/booking/
     */
    static public function serviceMapping () {
        return [
            self::PRODUCT_SERVICEPAKKE => [self::ADDITIONAL_SERVICE_CASH_ON_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_SOCIAL_CONTROL],
            self::PRODUCT_BPAKKE_DOR_DOR => [self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_SIMPLE_DELIVERY, self::ADDITIONAL_SERVICE_DELIVERY_OPTION],
            self::PRODUCT_EKSPRESS09 => [self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_SATURDAY_DELIVERY],
            self::PRODUCT_PICKUP_PARCEL => [self::ADDITIONAL_SERVICE_CASH_ON_DELIVERY, self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_DELIVERY_OPTION],
            self::PRODUCT_PICKUP_PARCEL_BULK => [self::ADDITIONAL_SERVICE_CASH_ON_DELIVERY, self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_DELIVERY_OPTION],
            self::PRODUCT_HOME_DELIVERY_PARCEL => [self::ADDITIONAL_SERVICE_CASH_ON_DELIVERY, self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION],
            self::PRODUCT_BUSINESS_PARCEL => [self::ADDITIONAL_SERVICE_CASH_ON_DELIVERY, self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_PHONE_NOTIFICATION, self::ADDITIONAL_SERVICE_DELIVERY_INDOORS],
            self::PRODUCT_BUSINESS_PARCEL_BULK => [self::ADDITIONAL_SERVICE_CASH_ON_DELIVERY, self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_PHONE_NOTIFICATION, self::ADDITIONAL_SERVICE_DELIVERY_INDOORS],
            self::PRODUCT_EXPRESS_NORDIC_0900_BULK => [self::ADDITIONAL_SERVICE_CASH_ON_DELIVERY, self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_PHONE_NOTIFICATION, self::ADDITIONAL_SERVICE_DELIVERY_INDOORS],
            self::PRODUCT_BUSINESS_PALLET => [self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_PHONE_NOTIFICATION, self::ADDITIONAL_SERVICE_DELIVERY_INDOORS],
            self::PRODUCT_BUSINESS_PARCEL_HALFPALLET => [self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_PHONE_NOTIFICATION, self::ADDITIONAL_SERVICE_DELIVERY_INDOORS],
            self::PRODUCT_BUSINESS_PARCEL_QUARTERPALLET => [self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_PHONE_NOTIFICATION, self::ADDITIONAL_SERVICE_DELIVERY_INDOORS],
            self::PRODUCT_EXPRESS_NORDIC_0900 => [self::ADDITIONAL_SERVICE_FLEX_DELIVERY, self::ADDITIONAL_SERVICE_RECIPIENT_NOTIFICATION, self::ADDITIONAL_SERVICE_PHONE_NOTIFICATION, self::ADDITIONAL_SERVICE_DELIVERY_INDOORS]
        ];
    }

    public function setId ($id) {
        if (!in_array($id, self::validProducts())) {
            throw new \InvalidArgumentException("$id is not a valid product. Valid products are: " . implode(', ', self::VALID_PRODUCTS));
        }
        $this->setData('id', $id);
    }

    public function setCustomerNumber ($customerNumber) {
        $this->setData('customerNumber', $customerNumber);
    }

    public function addService ($service) {
        $this->addData('services', $service);
    }

    public function validate()
    {
        if (!$this->containsData('id') || !$this->getData('id')) {
            throw new DataValidationException('BookingRequest\Consignment\Product requires "id" to be set.');
        }
        if (!$this->containsData('customerNumber') || !$this->getData('customerNumber')) {
            throw new DataValidationException('BookingRequest\Consignment\Product requires "customerNumber" to be set.');
        }

        // Check service mapping..
        $packageId = $this->getData('id');
        if ($services = $this->getData('services')) {
            $map = self::serviceMapping();
            $allowed_services = $map[$packageId];
            foreach ($services as $service) {
                if (!in_array($service, $allowed_services)) {
                    throw new DataValidationException('BookingRequest\Consignment\Product has invalid service set ("'.$service.'"). Allowed services are: ' . implode(',', $allowed_services));
                }
            }
        }


    }
}