define([
    'Ekm/Service',
    'KeyPress'
], function (
    service,
    KeyPress
) {
    var DomListener = function() {};

    DomListener.DOM_SELECTOR_LINK_ACCOUNT = '#ekm-link-account';

    DomListener.prototype.init = function(accountId)
    {
        $(DomListener.DOM_SELECTOR_LINK_ACCOUNT).off('click').on('click', function(){
            service.save(accountId);
        });

        $(service.getDomSelectorUsernameId()).off('keypress').on('keypress', function(event) {
            if (event.which != KeyPress.ENTER) {
                return;
            }
            service.save(accountId);
        });

        $(service.getDomSelectorPasswordId()).off('keypress').on('keypress', function(event) {
            if (event.which != KeyPress.ENTER) {
                return;
            }
            service.save(accountId);
        });
    };

    return new DomListener();
});