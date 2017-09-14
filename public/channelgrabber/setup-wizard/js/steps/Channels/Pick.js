//public version
define(['AjaxRequester', 'popup/mustache'], function(ajaxRequester, Popup)
{
    function Pick(notifications, addUri)
    {
        var popup;

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getNotifications = function()
        {
            return notifications;
        };

        this.getAddUri = function()
        {
            return addUri;
        };

        this.getPopup = function()
        {
            return popup;
        };

        this.setPopup = function(newPopup)
        {
            popup = newPopup;
            return this;
        };

        var init = function()
        {
            this.listenForChannelClick();
        };
        init.call(this);
    }

    Pick.SELECTOR_CHANNEL = '.setup-wizard-channel-badge';
    Pick.POPUP_WIDTH_PX = 500;
    Pick.POPUP_HEIGHT_PX = 180;

    Pick.prototype.listenForChannelClick = function()
    {
        var self = this;
        $(Pick.SELECTOR_CHANNEL).click(function()
        {
            var channelBadge = this;
            var channel = $(channelBadge).data('channel');
            var printName = $(channelBadge).data('print_name');
            var integrationType = $(channelBadge).data('integration_type');
            var region = $(channelBadge).data('region');
            self.addChannel(channel, printName, integrationType, region);
        });
    };

    Pick.prototype.addChannel = function(channel, printName, integrationType, region)
    {
        var self = this;

        if (integrationType === 'automated') {
            this.getNotifications().notice('Adding channel');
        }

        var uri = this.getAddUri();
        var data = {'channel' : channel, 'printName' : printName, 'integrationType' : integrationType, 'region' : region};

        this.getAjaxRequester().sendRequest(uri, data, function(data)
        {
            if (data['url'] !== null) {
                window.location = data['url'];
                return;
            }

            var templateUrlMap = {};
            if (integrationType === 'classic') {
                templateUrlMap.popup = '/cg-built/settings/template/Messages/channelAddClassicIntegrationMessage.mustache';
            } else if (integrationType === 'third-party') {
                templateUrlMap.popup = '/cg-built/settings/template/Messages/channelAddThirdPartyIntegrationMessage.mustache';
            } else if (integrationType === 'unsupported') {
                templateUrlMap.popup = '/cg-built/settings/template/Messages/channelAddUnsupportedIntegrationMessage.mustache';
            }

            Intercom('trackEvent', 'User attempted to add ' + integrationType + ' channel');

            self.setPopup(new Popup('', Pick.POPUP_WIDTH_PX, Pick.POPUP_HEIGHT_PX));
            self.renderPopup(templateUrlMap, {name: printName});
        });
    };

    Pick.prototype.renderPopup = function(templateUrlMap, data)
    {
        var self = this;
        CGMustache.get().fetchTemplates(templateUrlMap, function (templates, cgMustache) {
            var content = cgMustache.renderTemplate(templates, {name: data.name}, 'popup');
            self.getPopup().htmlContent(content);
            self.getPopup().show();
        });
    }

    return Pick;
});