define([
    'DomManipulator',
    'Ekm/Ajax'
], function (
    domManipulator,
    storage
) {
    var Service = function () {};

    Service.DOM_SELECTOR_USERNAME_ID = '#ekm-username';
    Service.DOM_SELECTOR_PASSWORD_ID = '#ekm-password';

    Service.prototype.save = function(accountId)
    {
        storage.save(
            domManipulator.getValue(Service.DOM_SELECTOR_USERNAME_ID),
            domManipulator.getValue(Service.DOM_SELECTOR_PASSWORD_ID),
            accountId
        );
    };

    Service.prototype.getDomSelectorUsernameId = function()
    {
        return Service.DOM_SELECTOR_USERNAME_ID;
    };

    Service.prototype.getDomSelectorPasswordId = function()
    {
        return Service.DOM_SELECTOR_PASSWORD_ID;
    };

    return new Service();
});