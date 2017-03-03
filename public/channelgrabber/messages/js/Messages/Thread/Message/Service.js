define([
    'Messages/Thread/Message/Storage/Ajax'
], function(
    storage
) {
    var Service = function()
    {
        this.getStorage = function()
        {
            return storage;
        };
    };

    Service.COLLAPSIBLE_SECTION_CLASS = 'collapsible-section';

    Service.prototype.sendMessage = function(thread, messageBody, callback)
    {
        var data = {threadId: thread.getId(), body: messageBody};
        this.getStorage().saveData(data, callback);
    };

    Service.prototype.wrapCollapsibleSections = function (messageBody) {

        var regex = /((?:^\>.*?$[\r\n]*)+)/gm;
        var replace =
            '<div class="message-collapser-wrap">' +
                '<div class="message-section-collapser" title="Toggle Hidden Lines">' +
                    '<div class="message-collapser-img-wrap"><img src="/channelgrabber/zf2-v4-ui/img/transparent-square.gif"></div>' +
                '</div>' +
                '<span class="' + Service.COLLAPSIBLE_SECTION_CLASS + '">$&</span>' +
            '</div>';

        return messageBody.replace(regex, replace);
    };

    Service.prototype.checkForCollapsibleSections = function (messageBody) {
        return messageBody.includes(Service.COLLAPSIBLE_SECTION_CLASS);
    };

    return new Service();
});