define(['WooCommerce/Service', 'KeyPress'], function(service, KeyPress) {
    var DomListener = function() {};

    DomListener.DOM_SELECTOR_ACCOUNT_LINK = '#woocommerce-link-account';

    DomListener.prototype.init = function(saveUrl, accountId) {
        $(DomListener.DOM_SELECTOR_ACCOUNT_LINK).off('click').on('click', function() {
            service.save(saveUrl, accountId);
        });

        $([service.getDomSelectorHost(), service.getDomSelectorKey(), service.getDomSelectorSecret()]).each(function(index, selector) {
            $(selector).off('keypress').on('keypress', function(event) {
                if (event.which != KeyPress.ENTER) {
                    return;
                }
                service.save(saveUrl, accountId);
            });
        });
    };

    return new DomListener();
});
