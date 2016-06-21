define(['AjaxRequester'], function(ajaxRequester)
{
    function Pick(notifications, addUri)
    {
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

        var init = function()
        {
            this.listenForChannelClick();
        };
        init.call(this);
    }

    Pick.SELECTOR_CHANNEL = '.setup-wizard-channel-badge';

    Pick.prototype.listenForChannelClick = function()
    {
        var self = this;
        $(Pick.SELECTOR_CHANNEL).click(function()
        {
            var channelBadge = this;
            var channel = $(channelBadge).data('channel');
            var region = $(channelBadge).data('region');
            self.addChannel(channel, region);
        });
    };

    Pick.prototype.addChannel = function(channel, region)
    {
        this.getNotifications().notice('Adding channel');
        var uri = this.getAddUri();
        var data = {'channel' : channel, 'region' : region};
        this.getAjaxRequester().sendRequest(uri, data, function(data)
        {
            window.location = data['url'];
        });
    };

    return Pick;
});