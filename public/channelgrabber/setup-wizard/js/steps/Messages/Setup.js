define([], function()
{
    function Setup()
    {
        var init = function()
        {
            this.listenForButtonClicks()
                .listenForAmazonSettingsButtonClick()
                .selectEmailText();
        };
        init.call(this);
    }

    Setup.prototype.listenForButtonClicks = function()
    {
        $('.setup-wizard-messages-amazon-button').click(function()
        {
            window.location = $(this).find('.action').data('action');
        });
        return this;
    };

    Setup.prototype.listenForAmazonSettingsButtonClick = function()
    {
        $('#setup-wizard-messaging-amazon-settings-button').click(function()
        {
            window.open($(this).data('action'));
        });
        return this;
    };

    Setup.prototype.selectEmailText = function()
    {
        $('#setup-wizard-messages-amazon-setup-instructions .inputbox').get(0).select();
        return this;
    };

    return Setup;
});