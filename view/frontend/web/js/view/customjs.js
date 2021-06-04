/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, ko, Component, quote) {
        "use strict";
        $(document).on('click', '#simplified_delivery_check', function(event) {
            // event.preventDefault();
            var method = quote.shippingMethod();
            // console.log(method);
            if($(this).is(':checked')){
                method.method_title='Pakke i postkassen (Pose på døren) (1 day)';
                $("#label_method_3584_bring").text(method.method_title);
            }else{
                method.method_title='Pakke i postkassen (1 day)';
                $("#label_method_3584_bring").text(method.method_title);
            }

        });
        return Component.extend({
            defaults: {
                template: 'Markant_Bring/markanttemp'
            },
            isAdditionalService: false,
            initialize: function () {
                this._super();
                this.selectedMethod = ko.computed(function() {
                    var method = quote.shippingMethod();
                    var selectedMethod = method != null ?  method.method_code : null;
                    
                    // console.log(method);
                    var default_title='Pakke i postkassen';
                    if(selectedMethod == "PAKKE_I_POSTKASSEN"){
                        // method.method_title='Ali Fareedi';
                        setTimeout(function() { 
                            $("#simplified_delivery").show();
                        }, 2000);
                        
                    }else{
                        $("#simplified_delivery").hide();
                        $("#simplified_delivery_check").prop("checked", false);
                    }


                }, this);

                
                return this;
            },
        });
        
    }
);
