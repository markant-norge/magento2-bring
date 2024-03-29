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
            
            var mt=method.method_title;
            var dateonly = mt.match(/\((.*)\)/);
            // console.log(mt);
            if($(this).is(':checked')){
                
                method.method_title='Pakke i postkassen [Pose på døren] '+dateonly[0];
                $("#label_method_3584_bring").text(method.method_title);
            }else{
                // console.log(dateonly[0]);
                method.method_title='Pakke i postkassen '+dateonly[0];
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
                    
                    if(selectedMethod == "3584"){
                        // method.method_title='Ali Fareedi';
                        
                        setTimeout(function() { 
                            $("#additional_services").show();
                            $("#simplified_delivery").show();
                        }, 2000);
                        
                    }else{
                        $("#additional_services").hide();
                        $("#simplified_delivery").hide();
                        $("#simplified_delivery_check").prop("checked", false);
                    }


                }, this);

                
                return this;
            },
        });
        
    }
);
