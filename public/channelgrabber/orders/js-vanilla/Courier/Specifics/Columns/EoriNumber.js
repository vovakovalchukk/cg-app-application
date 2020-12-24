define(['./ServiceDependantOptionsAbstract.js'], function(ServiceDependantOptionsAbstract)
{
    function EoriNumber(templatePath)
    {
        ServiceDependantOptionsAbstract.call(this, templatePath);

        var init = function()
        {
            this.listenForServiceChanges();
        };
        init.call(this);
    }

    EoriNumber.SELECTOR_EORI_NUMBER_PREFIX = '.courier-eori-number_';
    EoriNumber.SELECTOR_EORI_NUMBER_CONTAINER = '.courier-eori-number-options';

    EoriNumber.prototype = Object.create(ServiceDependantOptionsAbstract.prototype);

    EoriNumber.prototype.getSelectedValue = function(orderId)
    {
        return $(EoriNumber.SELECTOR_EORI_NUMBER_PREFIX + orderId + ' input').val();
    };

    EoriNumber.prototype.getContainer = function(orderId)
    {
        return $(EoriNumber.SELECTOR_EORI_NUMBER_PREFIX + orderId)
            .closest(EoriNumber.SELECTOR_EORI_NUMBER_CONTAINER);
    };

    EoriNumber.prototype.getOptionName = function()
    {
        return 'eoriNumbers';
    };

    EoriNumber.prototype.preventUpdateOptions = function(orderId)
    {
        // NO updates as the EORI number is NOT dependant on the selected services
        return false;
    };

    EoriNumber.prototype.renderNewOptions = function() {
        // Never render new fields as no updates will be done here
        return false;
    }

    return EoriNumber;
});
