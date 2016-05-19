<?php
namespace Markant\Bring\Model\Tracking;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/19/16 12:11 PM
 */
class Tracking
{

    const TRACKING_ARRIVED_COLLECTION = 'ARRIVED_COLLECTION';
    const TRACKING_ARRIVED_DELIVERY = 'ARRIVED_DELIVERY';
    const TRACKING_CUSTOMS = 'CUSTOMS';
    const TRACKING_DELIVERED = 'DELIVERED';
    const TRACKING_COLLECTED = 'COLLECTED';
    const TRACKING_DELIVERY_CANCELLED = 'DELIVERY_CANCELLED';
    const TRACKING_DELIVERY_CHANGED = 'DELIVERY_CHANGED';
    const TRACKING_DELIVERY_ORDERED = 'DELIVERY_ORDERED';
    const TRACKING_DEVIATION = 'DEVIATION';
    const TRACKING_HANDED_IN = 'HANDED_IN';
    const TRACKING_INTERNATIONAL = 'INTERNATIONAL';
    const TRACKING_IN_TRANSIT = 'IN_TRANSIT';
    const TRACKING_NOTIFICATION_SENT = 'NOTIFICATION_SENT';
    const TRACKING_PRE_NOTIFIED = 'PRE_NOTIFIED';
    const TRACKING_READY_FOR_PICKUP = 'READY_FOR_PICKUP';
    const TRACKING_RETURN = 'RETURN';
    const TRACKING_TRANSPORT_TO_RECIPIENT = 'TRANSPORT_TO_RECIPIENT';



    static public function humanize ($status) {
        $human = __('Unknown status');
        switch ($status) {
            case self::TRACKING_DELIVERED: $human = __('Package has been delivered'); break;
            case self::TRACKING_IN_TRANSIT: $human = __('The shipment has been handed in at terminal and forwarded'); break;
            case self::TRACKING_TRANSPORT_TO_RECIPIENT: $human = __('Package has been loaded for delivery'); break;
            case self::TRACKING_NOTIFICATION_SENT: $human = __('Notification sent'); break;
            case self::TRACKING_PRE_NOTIFIED: $human = __('The consignment has not been received by Bring'); break;
            case self::TRACKING_READY_FOR_PICKUP: $human = __('Package has arrived at pickup point or pickup locker'); break;
            case self::TRACKING_RETURN: $human = __('Return'); break;
            case self::TRACKING_ARRIVED_COLLECTION: $human = __('Arrived collection'); break;
            case self::TRACKING_ARRIVED_DELIVERY: $human = __('Arrived delivery'); break;
            case self::TRACKING_CUSTOMS: $human = __('Awaiting customs'); break;
            case self::TRACKING_COLLECTED: $human = __('Collected'); break;
            case self::TRACKING_DELIVERY_CANCELLED: $human = __('Delivery cancelled'); break;
            case self::TRACKING_DELIVERY_ORDERED: $human = __('Delivery ordered'); break;
            case self::TRACKING_DEVIATION: $human = __('Deviation'); break;
            case self::TRACKING_HANDED_IN: $human = __('Handed in'); break;
            case self::TRACKING_INTERNATIONAL: $human = __('International'); break;
        }
        return $human;
    }

}