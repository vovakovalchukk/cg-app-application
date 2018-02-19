define([
    'Listing/Import/Service'
], function (
    service
) {
    var Refresh = function ()
    {
    };

    Refresh.SELECTOR_REFRESH_BUTTON = '#refresh-button';

    Refresh.prototype.init = function()
    {
        service.init();
        $(Refresh.SELECTOR_REFRESH_BUTTON).off('click').on('click', function() {
            service.displayPopup();
        });
    };

    return new Refresh();
});