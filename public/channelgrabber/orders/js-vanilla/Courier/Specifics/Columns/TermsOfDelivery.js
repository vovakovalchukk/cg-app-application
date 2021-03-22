define([''], function()
{
    function TermsOfDelivery()
    {
        const init = function()
        {
            this.listenForTODChanges();
        };
        init.call(this);
    }

    TermsOfDelivery.SELECTOR_BULK_ACTION_TOD_CHECKBOX_ID = '#courier-terms-of-delivery-checkbox';
    TermsOfDelivery.SELECTOR_TOD_CHECKBOX_CLASS = '.courier-order-termsOfDelivery';

    TermsOfDelivery.prototype.listenForTODChanges = function()
    {
        const self = this;
        $(document).on('change', TermsOfDelivery.SELECTOR_BULK_ACTION_TOD_CHECKBOX_ID, function()
        {
            if (this.checked) {
                self.checkAllCheckboxes();
                return true;
            }

            self.uncheckAllCheckboxes();
        });

        return this;
    };

    TermsOfDelivery.prototype.checkAllCheckboxes = function()
    {
        $(TermsOfDelivery.SELECTOR_TOD_CHECKBOX_CLASS).each(function () {
            $(this).prop('checked', true);
        });
        return this;
    };

    TermsOfDelivery.prototype.uncheckAllCheckboxes = function()
    {
        $(TermsOfDelivery.SELECTOR_TOD_CHECKBOX_CLASS).each(function () {
            $(this).prop('checked', false);
        });
        return this;
    };

    return TermsOfDelivery;
});
