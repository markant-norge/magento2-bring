<?php

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/31/16 1:26 PM
 */

namespace Markant\Bring\Model\BookingClientService;

use Magento\Catalog\Api\Data\ProductInterface;

class AdvancedPackageManager
{
    /** @var array  */
    protected $_items = [];

    protected $_attributeShippedIndividuallyCode;
    protected $_attributeWidthCode;
    protected $_attributeLengthCode;
    protected $_attributeHeightCode;

    protected $_builtInPackages = [];


    public function __construct(array $items) {
        $this->_items = $items;
    }



    public function calculate () {
        $packages = [];


        $packedIds = [];

        $currentPackage = null;

        /** @var \Magento\Quote\Model\Quote\Item  $item */
        $index = 0;
        while (count($packedIds) !== count($this->_items)) {
            if (!isset($this->_items[$index])) {
                $index = 0; // start again.
            }
            $item = $this->_items[$index];
            if ($item->getCustomAttribute($this->getAttributeShippedIndividuallyCode())->getValue()) {
                $pack = new Package();
                $pack->setItems([$item]);
                $pack->setWidth($item->getCustomAttribute($this->getAttributeWidthCode())->getValue());
                $pack->setHeight($item->getCustomAttribute($this->getAttributeHeightCode())->getValue());
                $pack->setLength($item->getCustomAttribute($this->getAttributeLengthCode())->getValue());
                $packages[] = $pack;
                $packedIds[] = $item->getId();
            } else {
                
            }
            $index++;
        }

        foreach ($this->_items as $item) {
            $weight = $item->getWeight();

        }
        return $packages;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * @param array $items
     * @return AdvancedPackageManager
     */
    public function setItems($items)
    {
        $this->_items = $items;
        return $this;
    }

    /**
     * @return array
     */
    public function getBuiltInPackages()
    {
        return $this->_builtInPackages;
    }

    /**
     * @param array $builtInPackages
     * @return AdvancedPackageManager
     */
    public function setBuiltInPackages($builtInPackages)
    {
        $this->_builtInPackages = $builtInPackages;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributeShippedIndividuallyCode()
    {
        return $this->_attributeShippedIndividuallyCode;
    }

    /**
     * @param mixed $attributeShippedIndividuallyCode
     * @return AdvancedPackageManager
     */
    public function setAttributeShippedIndividuallyCode($attributeShippedIndividuallyCode)
    {
        $this->_attributeShippedIndividuallyCode = $attributeShippedIndividuallyCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributeWidthCode()
    {
        return $this->_attributeWidthCode;
    }

    /**
     * @param mixed $attributeWidthCode
     * @return AdvancedPackageManager
     */
    public function setAttributeWidthCode($attributeWidthCode)
    {
        $this->_attributeWidthCode = $attributeWidthCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributeLengthCode()
    {
        return $this->_attributeLengthCode;
    }

    /**
     * @param mixed $attributeLengthCode
     * @return AdvancedPackageManager
     */
    public function setAttributeLengthCode($attributeLengthCode)
    {
        $this->_attributeLengthCode = $attributeLengthCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttributeHeightCode()
    {
        return $this->_attributeHeightCode;
    }

    /**
     * @param mixed $attributeHeightCode
     * @return AdvancedPackageManager
     */
    public function setAttributeHeightCode($attributeHeightCode)
    {
        $this->_attributeHeightCode = $attributeHeightCode;
        return $this;
    }





}