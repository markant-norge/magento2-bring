<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Markant\Bring\Model\Order\Shipment\Edi;

use Markant\Bring\Model\Order\Shipment\Edi;

/**
 * Class Validator
 */
class Validator
{
    /**
     * Required field
     *
     * @var array
     */
    protected $required = [
        'parent_id' => 'Parent Track Id',
        'order_id' => 'Order Id',
        'weight' => 'Weight',
        'length' => 'Length',
        'height' => 'Height',
        'width' => 'Width'
    ];

    /**
     * Validate data
     *
     * @param \Markant\Bring\Model\Order\Shipment\Edi $edi
     * @return array
     */
    public function validate(Edi $edi)
    {
        $errors = [];
        $commentData = $edi->getData();
        foreach ($this->required as $code => $label) {
            if (!$edi->hasData($code)) {
                $errors[$code] = sprintf('%s is a required field', $label);
            } elseif (empty($commentData[$code])) {
                $errors[$code] = sprintf('%s can not be empty', $label);
            }
        }

        return $errors;
    }
}
