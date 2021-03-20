<?php

namespace Markant\Bring\Helper;

/**
 * Class Data helper
 */
class Data
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serialize;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Data constructor.
     * @param \Magento\Framework\Serialize\SerializerInterface $serialize
     */
    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serialize,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    )
    {
        $this->serialize = $serialize;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Will try to use PHP unserialize and json_decode to parse data string
     *
     * @param type $serializedData
     * @param type $returnIfInvalid value to be returned if no valid value is found
     */
    public function unserialize($serializedData, $returnIfInvalid = false)
    {

        if (empty($serializedData))
            return $returnIfInvalid;

        $res1 = $this->serialize->unserialize($serializedData);
        if ($res1 !== false) {
            return $res1;
        }

        $res2 = $this->jsonHelper->jsonDecode($serializedData);
        if ($res2 !== false) {
            return $res2;
        }

        return $returnIfInvalid;
    }

}