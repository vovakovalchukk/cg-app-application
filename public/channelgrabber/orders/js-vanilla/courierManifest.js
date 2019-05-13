define([
    'Orders/OrdersBulkActionAbstract',
    'Orders/EventHandler/CourierManifest',
    'popup/generic',
    'popup/confirm',
    'cg-mustache'
], function(
    OrdersBulkActionAbstract,
    EventHandler,
    Popup,
    Confirm,
    CGMustache
) {
    function CourierManifest(templateMap)
    {
        OrdersBulkActionAbstract.call(this);

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

        var init = function()
        {
            eventHandler = new EventHandler(this);
        };
        init.call(this);
    }

    CourierManifest.prototype = Object.create(OrdersBulkActionAbstract.prototype);

    CourierManifest.URL_GET_ACCOUNTS = '/orders/courier/manifest/accounts';
    CourierManifest.URL_GET_DETAILS = '/orders/courier/manifest/details';
    CourierManifest.URL_GET_HISTORIC = '/orders/courier/manifest/historic';
    CourierManifest.URL_GENERATE = '/orders/courier/manifest';
    CourierManifest.POPUP_WIDTH_PX = 500;
    CourierManifest.SELECTOR_GENERATE_SECTION = '.courier-manifest-generate';
    CourierManifest.SELECTOR_GENERATE_FORM = '.courier-manifest-generate-form';
    CourierManifest.SELECTOR_HISTORIC_SECTION = '.courier-manifest-historic';
    CourierManifest.SELECTOR_HISTORIC_MONTHS = '.courier-manifest-historic-months';
    CourierManifest.SELECTOR_HISTORIC_DATES = '.courier-manifest-historic-dates';
    CourierManifest.SELECTOR_PRINT_FORM = '.courier-manifest-print-form';

    CourierManifest.prototype.invoke = function()
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
        this.getNotificationHandler().notice('Loading manifest details');
        var popup = new Popup('', CourierManifest.POPUP_WIDTH_PX);
        this.setPopup(popup);

        this.getAjaxRequester().sendRequest(CourierManifest.URL_GET_ACCOUNTS, {}, function(response)
        {
            self.renderPopup(response);
        }, function(response)
        {
            self.ajaxError(response);
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
            self.getEventHandler().listenForAccountSelect();
            if (data.selectedAccount) {
                self.setSelectedAccountId(data.selectedAccount);
            }
            if (data.details) {
                self.renderDetails(data);
            }
            self.getPopup().show();
            self.getNotificationHandler().clearNotifications();
        });
    };

    CourierManifest.prototype.accountSelected = function(accountId)
    {
        var self = this;
        this.setSelectedAccountId(accountId);
        this.getNotificationHandler().notice('Loading courier manifest details');
        $(CourierManifest.SELECTOR_GENERATE_SECTION).empty();
        $(CourierManifest.SELECTOR_HISTORIC_SECTION).empty();
        this.getAjaxRequester().sendRequest(CourierManifest.URL_GET_DETAILS, {"account": accountId}, function(response)
        {
            self.renderDetails(response);
            self.getNotificationHandler().clearNotifications();
        }, function(response)
        {
            self.ajaxError(response);
        });
    };
    
    CourierManifest.prototype.showLoading = function(selector)
    {
        var loader = '<img src="/channelgrabber/zf2-v4-ui/img/loading.gif" class="b-loader" />';
        $(selector).empty().append(loader);
    };

    CourierManifest.prototype.renderDetails = function(data)
    {
        this.setSelectedAccountDetails(data.details)
            .renderGenerateSection(data.details)
            .renderHistoricSection(data.historic);
    };

    CourierManifest.prototype.renderGenerateSection = function(details)
    {
        var templates = this.getTemplates();
        var generateButton = CGMustache.get().renderTemplate(templates, {
            "buttons": [{
                "id": EventHandler.SELECTOR_GENERATE_BUTTON.replace('#', ''),
                "value": "Generate",
                "disabled": (details.openOrders == 0 || (details.oncePerDay && details.manifestedToday))
            }]
        }, 'buttons');

        var content = CGMustache.get().renderTemplate(templates, {"openOrders": details.openOrders}, 'popupGenerate', {"generateButton": generateButton});
        $(CourierManifest.SELECTOR_GENERATE_SECTION).html(content);
        this.getEventHandler().listenForGenerateButtonClick();
        return this;
    };

    CourierManifest.prototype.renderHistoricSection = function(historic)
    {
        if (!historic.yearOptions || historic.yearOptions.length == 0) {
            return this;
        }
        var templates = this.getTemplates();
        var historicYearSelect = CGMustache.get().renderTemplate(templates, {
            "id": EventHandler.SELECTOR_HISTORIC_YEARS.replace('#', ''),
            "name": EventHandler.SELECTOR_HISTORIC_YEARS.replace('#', ''),
            "options": historic.yearOptions
        }, 'select');

        var content = CGMustache.get().renderTemplate(templates, {}, 'popupHistoric', {"historicYearSelect": historicYearSelect});
        $(CourierManifest.SELECTOR_HISTORIC_SECTION).html(content);
        this.getEventHandler().listenForHistoricYearSelect();
        if (historic.monthOptions) {
            this.renderHistoricMonths(historic);
        }
        return this;
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
        var self = this;
        var accountId = this.getSelectedAccountId();
        this.getNotificationHandler().notice('Generating manifest, this might take a few moments');
        this.getPopup().hide();
        this.getAjaxRequester().sendRequest(CourierManifest.URL_GENERATE, {"account": accountId}, function(response)
        {
            if (response.id) {
                self.getNotificationHandler().success('Manifest generated successfully, now downloading...');
                self.sendPrintManifestRequest(response.id);
            } else {
                self.getNotificationHandler().success('Manifest generated and sent to courier successfully.');
            }
            self.removePopup();
        }, function(response)
        {
            self.ajaxError(response);
        });
    };

    CourierManifest.prototype.sendPrintManifestRequest = function(manifestId)
    {
        var accountId = this.getSelectedAccountId();
        var uri = CourierManifest.URL_GENERATE + '/' + manifestId;
        $(CourierManifest.SELECTOR_GENERATE_FORM).attr('action', uri);
        $(CourierManifest.SELECTOR_GENERATE_FORM + ' input[name="account"').val(accountId);
        $(CourierManifest.SELECTOR_GENERATE_FORM).submit();
    };

    CourierManifest.prototype.closeManifestPopupAsNoOrdersToProcess = function()
    {
        this.getNotificationHandler().notice('Open orders have not been found to generate manifest');
        this.getPopup().hide();
        this.removePopup();
    };

    CourierManifest.prototype.historicYearSelected = function(year)
    {
        var self = this;
        this.getNotificationHandler().notice('Loading historic manifest month options');
        $(CourierManifest.SELECTOR_HISTORIC_MONTHS).empty();
        $(CourierManifest.SELECTOR_HISTORIC_DATES).empty();
        var data = {
            "account": this.getSelectedAccountId(),
            "year": year
        };
        this.getAjaxRequester().sendRequest(CourierManifest.URL_GET_HISTORIC, data, function(response)
        {
            self.renderHistoricMonths(response.historic);
            self.getNotificationHandler().clearNotifications();
        }, function(response)
        {
            self.ajaxError(response);
        });
    };

    CourierManifest.prototype.renderHistoricMonths = function(data)
    {
        if (!data.monthOptions || data.monthOptions.length == 0) {
            return this;
        }
        var templates = this.getTemplates();
        var historicMonthSelect = CGMustache.get().renderTemplate(templates, {
            "id": EventHandler.SELECTOR_HISTORIC_MONTHS.replace('#', ''),
            "name": EventHandler.SELECTOR_HISTORIC_MONTHS.replace('#', ''),
            "options": data.monthOptions
        }, 'select');

        $(CourierManifest.SELECTOR_HISTORIC_MONTHS).html(historicMonthSelect);
        this.getEventHandler().listenForHistoricMonthSelect();
        if (data.dateOptions) {
            this.renderHistoricDates(data);
        }
    };

    CourierManifest.prototype.historicMonthSelected = function(month, year)
    {
        var self = this;
        this.getNotificationHandler().notice('Loading historic manifest date options');
        $(CourierManifest.SELECTOR_HISTORIC_DATES).empty();
        var data = {
            "account": this.getSelectedAccountId(),
            "year": year,
            "month": month
        };
        this.getAjaxRequester().sendRequest(CourierManifest.URL_GET_HISTORIC, data, function(response)
        {
            self.renderHistoricDates(response.historic);
            self.getNotificationHandler().clearNotifications();
        }, function(response)
        {
            self.ajaxError(response);
        });
    };

    CourierManifest.prototype.renderHistoricDates = function(data)
    {
        if (!data.dateOptions || data.dateOptions.length == 0) {
            return this;
        }
        var templates = this.getTemplates();
        var historicDateSelect = CGMustache.get().renderTemplate(templates, {
            "id": EventHandler.SELECTOR_HISTORIC_DATES.replace('#', ''),
            "name": EventHandler.SELECTOR_HISTORIC_DATES.replace('#', ''),
            "options": data.dateOptions
        }, 'select');

        $(CourierManifest.SELECTOR_HISTORIC_DATES).html(historicDateSelect);
        this.getEventHandler().listenForHistoricDateSelect();
    };

    CourierManifest.prototype.historicManifestSelected = function(manifestId)
    {
        this.getNotificationHandler().notice('Downloading manifest', true);
        this.getPopup().hide();
        this.sendPrintManifestRequest(manifestId);
    };

    CourierManifest.prototype.ajaxError = function(response)
    {
        this.removePopup();
        this.getNotificationHandler().ajaxError(response);
    };

    CourierManifest.prototype.removePopup = function()
    {
        this.getPopup().hide().getElement().remove();
        this.setPopup(null);
        return this;
    };

    return CourierManifest;
});