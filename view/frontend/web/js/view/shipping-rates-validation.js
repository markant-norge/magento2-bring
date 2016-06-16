/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        bringShippingProviderShippingRatesValidator,
        bringShippingProviderShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('bring', bringShippingProviderShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('bring', bringShippingProviderShippingRatesValidationRules);
        return Component;
    }
);
