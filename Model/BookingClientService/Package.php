<?php
namespace Markant\Bring\Model\BookingClientService;

/**
 * Copyright (C) Markant Norge AS - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @author petterk
 * @date 5/31/16 1:16 PM
 */
class Package
{

    protected $height;

    protected $length;

    protected $width;

    protected $_factorAttributeCode;

    /** @var array  */
    protected $_items = [];


    public function __construct () {

    }


    public function getWeight()
    {
        $weight = 0;
        /** @var \Magento\Catalog\Api\Data\ProductInterface $item */
        foreach ($this->_items as $item) {
            $weight += $item->getWeight();
        }
        return $weight;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     * @return Package
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     * @return Package
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     * @return Package
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFactorAttributeCode()
    {
        return $this->_factorAttributeCode;
    }

    /**
     * @param mixed $factorAttributeCode
     * @return Package
     */
    public function setFactorAttributeCode($factorAttributeCode)
    {
        $this->_factorAttributeCode = $factorAttributeCode;
        return $this;
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
     * @return Package
     */
    public function setItems($items)
    {
        $this->_items = $items;
        return $this;
    }

}