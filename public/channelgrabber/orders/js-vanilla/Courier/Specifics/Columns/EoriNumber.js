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
    EoriNumber.SELECTOR_ORDER_LABEL_STATUS_TPL = '#datatable input[name="orderInfo[_orderId_][labelStatus]"]';

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

    return EoriNumber;
});
