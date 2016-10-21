define(function() {
    function DeliveryExperience()
    {
        this.init();
    }

    DeliveryExperience.SELECTOR_SERVICE_SELECT = '.courier-service-custom-select';
    DeliveryExperience.SELECTOR_CURRENT_ROW = 'tr';
    DeliveryExperience.SELECTOR_DELIVERY_EXPERIENCE_DIV = '.deliveryExperience';
    DeliveryExperience.SELECTOR_TEXT = 'span';
    DeliveryExperience.SELECTOR_INPUT = 'input';

    DeliveryExperience.prototype.init = function()
    {
        var self = this;
        $(document).on('change', DeliveryExperience.SELECTOR_SERVICE_SELECT, function(event, element, value) {
            var row = $(element).closest(DeliveryExperience.SELECTOR_CURRENT_ROW);
            self.update(value, $(DeliveryExperience.SELECTOR_DELIVERY_EXPERIENCE_DIV, row));
        });
    };

    DeliveryExperience.prototype.update = function(service, deliveryExperienceDiv)
    {
        var span = $(DeliveryExperience.SELECTOR_TEXT, deliveryExperienceDiv).html("");
        var input = $(DeliveryExperience.SELECTOR_INPUT, deliveryExperienceDiv).val("");
        var deliveryExperiences = input.data("delivery-experiences");

        if (service in deliveryExperiences) {
            var deliveryExperience = deliveryExperiences[service];
            span.html(deliveryExperience["deliveryExperienceText"]);
            input.val(deliveryExperience["deliveryExperience"]);
        }
    };

    return DeliveryExperience;
});