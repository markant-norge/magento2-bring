<?php
namespace Markant\Bring\Model\BookingClientService;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/31/16 1:26 PM
 */


use Magento\Catalog\Api\Data\ProductInterface;


/**
 * Class AdvancedPackageManager
 *
 * Note in use, for future ...
 *
 * @package Markant\Bring\Model\BookingClientService
 */
class AdvancedPackageManager
{
    /** @var array  */
    protected $_items = [];

    protected $_attributeShippedIndividuallyCode;
    protected $_attributeWidthCode;
    protected $_attributeLengthCode;
    protected $_attributeHeightCode;

    protected $_defaultWidth;
    protected $_defaultHeight;
    protected $_defaultLength;
    protected $_defaultWeight;

    protected $_builtInPackages = [];


    public function __construct(array $items) {
        $this->_items = $items;
    }

    public function getItemDimensions (\Magento\Quote\Model\Quote\Item $item) {
        $width = null;
        $height = null;
        $length = null;
        
        $product = $item->getProduct();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product2 = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
        if ($this->getAttributeWidthCode()) {
            $width = $product2->getData($this->getAttributeWidthCode());
        }
        if ($this->getAttributeHeightCode()) {
            $height = $product2->getData($this->getAttributeHeightCode());
        }
        if ($this->getAttributeLengthCode()) {
            $length = $product2->getData($this->getAttributeLengthCode());
        }


        if (!$width) {
            $width = $this->getDefaultWidth();
        }

        if (!$height) {
            $height = $this->getDefaultHeight();
        }
        if (!$length) {
            $length = $this->getDefaultLength();
        }

        return [
            'width' => $width,
            'height' => $height,
            'length' => $length
        ];
    }


    public function calculate () {
        $packages = [];
        $shipAloneAttribute = $this->getAttributeShippedIndividuallyCode();


        $currentPackage = new Package();
        $currentPackage->setWidth($this->getDefaultWidth());
        $currentPackage->setHeight($this->getDefaultHeight());
        $currentPackage->setLength($this->getDefaultLength());


        /** @var \Magento\Quote\Model\Quote\Item  $item */
        foreach ($this->_items as $item) {
            $product = $item->getProduct();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $product2 = $objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
            if ($shipAloneAttribute && $product2->getData($shipAloneAttribute)) {
                $pack = new Package();
                $pack->setItems([$item]);
                $dimension = $this->getItemDimensions($item);
                $pack->setWidth($dimension['width']);
                $pack->setHeight($dimension['height']);
                $pack->setLength($dimension['length']);
                $packages[] = $pack;
            } else {
                // Package all items into one box!
                $currentPackage->addItem($item);
                
            }
        }

        if ($currentPackage->getItems()) {
            $packages[] = $currentPackage;
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

    /**
     * @return mixed
     */
    public function getDefaultWidth()
    {
        return $this->_defaultWidth;
    }

    /**
     * @param mixed $defaultWidth
     * @return AdvancedPackageManager
     */
    public function setDefaultWidth($defaultWidth)
    {
        $this->_defaultWidth = $defaultWidth;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultLength()
    {
        return $this->_defaultLength;
    }

    /**
     * @param mixed $defaultLength
     * @return AdvancedPackageManager
     */
    public function setDefaultLength($defaultLength)
    {
        $this->_defaultLength = $defaultLength;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultHeight()
    {
        return $this->_defaultHeight;
    }

    /**
     * @param mixed $defaultHeight
     * @return AdvancedPackageManager
     */
    public function setDefaultHeight($defaultHeight)
    {
        $this->_defaultHeight = $defaultHeight;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getDefaultWeight()
    {
        return $this->_defaultWeight;
    }

    /**
     * @param mixed $defaultWeight
     */
    public function setDefaultWeight($defaultWeight)
    {
        $this->_defaultWeight = $defaultWeight;
        return $this;
    }




}