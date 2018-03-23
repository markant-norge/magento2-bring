<?php

namespace Markant\Bring\Helper;

/**
 * Class Data helper
 */
class Data {
    
    /**
     * Will try to use PHP unserialize and json_decode to parse data string
     * 
     * @param type $serializedData
     * @param type $returnIfInvalid value to be returned if no valid value is found
     */
    public static function unserialize($serializedData, $returnIfInvalid = false) {
        
        if (empty($serializedData))
            return $returnIfInvalid;
        
        $res1 = @unserialize($serializedData);
        if ($res1!==false) {
            return $res1;
        }
        
        $res2 = @json_decode($serializedData);
        if ($res2!==false) {
            return $res2;
        }
        
        return $returnIfInvalid;
    }

}