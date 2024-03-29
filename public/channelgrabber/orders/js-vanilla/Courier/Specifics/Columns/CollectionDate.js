define([''], function()
{
    function CollectionDate()
    {
        const init = function()
        {
            this.listenForCollectionDateChanges();
        };
        init.call(this);
    }

    CollectionDate.SELECTOR_BULK_ACTION_CD_INPUT_ID = '#bulk-courier-collection-date-datepicker';
    CollectionDate.SELECTOR_BULK_ACTION_CD_HIDDEN_INPUT_ID = '#bulk-courier-collection-date';
    CollectionDate.SELECTOR_CD_INPUT = 'input[id^="courier-order-collectionDate_"]';

    CollectionDate.prototype.listenForCollectionDateChanges = function()
    {
        const self = this;
        $(document).on('change', CollectionDate.SELECTOR_BULK_ACTION_CD_INPUT_ID, function()
        {
            let value = $(CollectionDate.SELECTOR_BULK_ACTION_CD_HIDDEN_INPUT_ID).val()
            self.updateAllCollectionDatesInputs(value);
        });

        return this;
    };

    CollectionDate.prototype.updateAllCollectionDatesInputs = function(value)
    {
        $(CollectionDate.SELECTOR_CD_INPUT).each(function () {
            $(this).val(value);
        });
        return this;
    };

    return CollectionDate;
});