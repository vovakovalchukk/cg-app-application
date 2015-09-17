define(['DomManipulator', 'WooCommerce/Ajax'], function(domManipulator, storage) {
    var Service = function() {};

    Service.DOM_SELECTOR_HOST = '#woocommerce-host';
    Service.DOM_SELECTOR_KEY = '#woocommerce-key';
    Service.DOM_SELECTOR_SECRET = '#woocommerce-secret';

    Service.prototype.save = function(url, accountId) {
        storage.save(
            url,
            accountId,
            domManipulator.getValue(Storage.DOM_SELECTOR_HOST),
            domManipulator.getValue(Storage.DOM_SELECTOR_KEY),
            domManipulator.getValue(Storage.DOM_SELECTOR_SECRET)
        );
    };

    Service.prototype.getDomSelectorHost = function() {
        return Service.DOM_SELECTOR_HOST;
    };

    Service.prototype.getDomSelectorKey = function() {
        return Service.DOM_SELECTOR_KEY;
    };

    Service.prototype.getDomSelectorSecret = function() {
        return Service.DOM_SELECTOR_SECRET;
    };

    return new Service();
});
