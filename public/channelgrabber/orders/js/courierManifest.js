define([
    './EventHandler/CourierManifest.js',
    'AjaxRequester',
    'popup/generic',
    'popup/confirm',
    'cg-mustache'
], function(
    EventHandler,
    ajaxRequester,
    Popup,
    Confirm,
    CGMustache
) {
    function CourierManifest(templateMap)
    {
        var eventHandler;
        var templates;
        var popup;
        var selectedAccountId;
        var selectedAccountDetails;

        this.getTemplateMap = function()
        {
            return templateMap;
        };

        this.getEventHandler = function()
        {
            return eventHandler;
        };

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getTemplates = function()
        {
            return templates;
        };

        this.setTemplates = function(newTemplates)
        {
            templates = newTemplates;
            return this;
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

        this.getSelectedAccountId = function()
        {
            return selectedAccountId;
        };

        this.setSelectedAccountId = function(newSelectedAccountId)
        {
            selectedAccountId = newSelectedAccountId;
            return this;
        };

        this.getSelectedAccountDetails = function()
        {
            return selectedAccountDetails;
        };

        this.setSelectedAccountDetails = function(newSelectedAccountDetails)
        {
            selectedAccountDetails = newSelectedAccountDetails;
            return this;
        };

        this.getNotifications = function()
        {
            return n;
        };

        var init = function()
        {
            eventHandler = new EventHandler(this);
        };
        init.call(this);
    }

    CourierManifest.URL_GET_ACCOUNTS = '/orders/courier/manifest/accounts';
    CourierManifest.URL_GET_DETAILS = '/orders/courier/manifest/details';
    CourierManifest.POPUP_WIDTH_PX = 450;
    CourierManifest.POPUP_HEIGHT_PX = 200;
    CourierManifest.SELECTOR_GENERATE_SECTION = '.courier-manifest-generate';
    CourierManifest.SELECTOR_GENERATE_FORM = '.courier-manifest-generate-form';

    CourierManifest.prototype.action = function(element)
    {
        if (this.getPopup()) {
            this.getPopup().show();
            return;
        }
        this.createPopup();
    };

    CourierManifest.prototype.createPopup = function()
    {
        var self = this;
        var popup = new Popup('', CourierManifest.POPUP_WIDTH_PX, CourierManifest.POPUP_HEIGHT_PX);
        this.setPopup(popup);
        popup.displayLoader();
        popup.show();

        this.getAjaxRequester().sendRequest(CourierManifest.URL_GET_ACCOUNTS, {}, function(response)
        {
            self.renderPopup(response);
        });
    };

    CourierManifest.prototype.renderPopup = function(data)
    {
        var self = this;
        CGMustache.get().fetchTemplates(this.getTemplateMap(), function(templates, cgMustache)
        {
            self.setTemplates(templates);
            var accountSelect = cgMustache.renderTemplate(templates, {
                "id": "courier-manifest-account-select",
                "name": "courier-manifest-account-select",
                "options": data.accounts
            }, 'select');
            var content = cgMustache.renderTemplate(templates, {}, 'popup', {"accountSelect": accountSelect});

            self.getPopup().htmlContent(content);
        });
    };

    CourierManifest.prototype.accountSelected = function(accountId)
    {
        var self = this;
        this.setSelectedAccountId(accountId);
        this.showLoading(CourierManifest.SELECTOR_GENERATE_SECTION);
        this.getAjaxRequester().sendRequest(CourierManifest.URL_GET_DETAILS, {"account": accountId}, function(response)
        {
            self.setSelectedAccountDetails(response);
            self.renderDetails(response);
        });
    };
    
    CourierManifest.prototype.showLoading = function(selector)
    {
        var loader = '<img src="/channelgrabber/zf2-v4-ui/img/loading.gif" class="b-loader" />';
        $(selector).empty().append(loader);
    };

    CourierManifest.prototype.renderDetails = function(details)
    {
        var templates = this.getTemplates();
        var generateButton = CGMustache.get().renderTemplate(templates, {
            "buttons": [{
                "id": EventHandler.SELECTOR_GENERATE_BUTTON.replace('#', ''),
                "value": "Generate Manifest",
                "disabled": (details.openOrders == 0 || (details.oncePerDay && details.manifestedToday))
            }]
        }, 'buttons');
        var content = CGMustache.get().renderTemplate(templates, {"openOrders": details.openOrders}, 'popupGenerate', {"generateButton": generateButton});

        $(CourierManifest.SELECTOR_GENERATE_SECTION).html(content);
    };

    CourierManifest.prototype.generateManifest = function()
    {
        var accountId = this.getSelectedAccountId();
        var accountDetails = this.getSelectedAccountDetails();
        if (!accountId || !accountDetails) {
            return;
        }
        if (accountDetails.oncePerDay) {
            this.confirmSendingGenerateManifestRequest();
        } else {
            this.sendGenerateManifestRequest();
        }
    };

    CourierManifest.prototype.confirmSendingGenerateManifestRequest = function()
    {
        var self = this;
        new Confirm(
            'You can only generate a manifest once a day for this carrier are sure you wish to proceed?',
            function(answer)
            {
                if (answer != Confirm.VALUE_YES) {
                    return;
                }
                self.sendGenerateManifestRequest();
            }
        );
    };

    CourierManifest.prototype.sendGenerateManifestRequest = function()
    {
        var accountId = this.getSelectedAccountId();
        this.getNotifications().notice('Generating manifest', true);
        $(CourierManifest.SELECTOR_GENERATE_FORM + ' input').val(accountId);
        $(CourierManifest.SELECTOR_GENERATE_FORM).submit();
    };

    return CourierManifest;
});