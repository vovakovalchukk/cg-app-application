define(
    ["popup/confirm","cg-mustache"], function (Confirm, CGMustache){
    var InvoiceSettings = function(hasAmazonAccount) {

        var container = '.invoiceSettings';
        var selector = container + ' .custom-select, #productImages';
        var defaultSettingsSelector = container + ' .invoiceDefaultSettings #defaultInvoiceCustomSelect input';
        var autoEmailSettingsSelector = container + ' .invoiceDefaultSettings #autoEmail';
        var productImagesSettingsSelector = container + ' .invoiceDefaultSettings #productImages';
        var tradingCompaniesAssignedInvoiceSelector = container + ' .invoiceTradingCompanySettings input.invoiceTradingCompaniesCustomSelect';
        var tradingCompaniesSendFromAddressSelector = container + ' .invoiceTradingCompanySettings input.invoiceSendFromAddressInput';
        var copyRequiredSelector = container + ' .invoiceDefaultSettings #copyRequired';
        var emailSendAsSelector = container + ' .invoiceDefaultSettings #emailSendAs';
        var emailBccSelector = container + ' .invoiceDefaultSettings #emailBcc';
        var emailVerifyButtonSelector = container + ' .email-verify';
        var emailVerifiedStatusSelector = '#emailVerifiedStatus';
        var emailInvoiceFieldsSelector = container + ' .emailInvoiceFields';
        var emailInvoiceNotificationSelector = '#emailInvoiceNotification';
        var saveSettingsButtonSelector = container + ' .settings-save';

        var emailInvoiceFields = $(emailInvoiceFieldsSelector);
        var emailBccField = $(emailBccSelector);
        var emailVerifyButton = $(emailVerifyButtonSelector);
        var emailVerifiedStatus = $(emailVerifiedStatusSelector);

        var init = function ()
        {
            var self = this;

            // Set field states
            setAutoEmail();
            setCopyRequired();

            // Set event listeners
            setSelectorEvents(self);
            setEmailSendAsEvents(self);
            setEmailVerifyButtonEvents(self);
            setSaveSettingsButtonEvents(self);
            setCopyRequiredEvents(self);
            setAutoEmailEvents(self);
            setEmailBccEvents(self);
        };

        function showConfirmationMessageForAmazonAccount(self)
        {
            var templateUrlMap = {
                message: '/cg-built/settings/template/Warnings/amazonEmailWarning.mustache'
            };

            CGMustache.get().fetchTemplates(templateUrlMap, function (templates, cgmustache) {
                var messageHTML = cgmustache.renderTemplate(templates, {}, "message");
                new Confirm(messageHTML, function (response) {
                    if (response == "Yes") {
                        emailVerifyButton.prop('disabled', true).addClass('verifying').text('Verifying...');
                        ajaxVerify(self);
                    }
                });
            });
        }

        function showConfirmationMessage()
        {
            var templateUrlMap = {
                message: '/cg-built/settings/template/Messages/emailInvoiceConfirmationMessage.mustache'
            };

            CGMustache.get().fetchTemplates(templateUrlMap, function (templates, cgmustache) {
                var messageHTML = cgmustache.renderTemplate(templates, {}, "message");
                new Confirm(messageHTML, null, ['Ok']);
                // popup.show();
            });
        }

        function ajaxSave(object)
        {
            object.save(object.handleSaveResponse);
        }

        function ajaxVerify(object)
        {
            object.save(object.handleVerifyResponse);
        }

        function setAutoEmail()
        {
            if ($('#autoEmail').prop('checked')) {
                emailInvoiceFields.removeClass('hidden');
            }
        }

        function setCopyRequired()
        {
            if (!$('#copyRequired').prop('checked')) {
                emailBccField.hide();
            }
        }

        function setSelectorEvents(self)
        {
            $(document).on('change', selector, function() {
                ajaxSave(self);
            })
        }

        function setEmailSendAsEvents(self)
        {
            $(document).on('change keyup', emailSendAsSelector, function () {
                emailVerifyButton.removeClass('hidden');
                $(emailVerifiedStatusSelector).remove();
            });
        }

        function setEmailVerifyButtonEvents(self)
        {
            $(document).on('click', emailVerifyButtonSelector, function (e) {
                e.preventDefault();

                if (getElementOnClickCheckedStatus('autoEmail') && hasAmazonAccount == true) {
                    showConfirmationMessageForAmazonAccount(self);
                } else {
                    ajaxVerify(self);
                }
            });
        }

        function setSaveSettingsButtonEvents(self)
        {
            $(document).on('click', saveSettingsButtonSelector, function (e) {
                e.preventDefault();
                ajaxSave(self);
            });
        }

        function setCopyRequiredEvents(self)
        {
            $(document).on('change', copyRequiredSelector, function () {
                emailBccField.toggle();

                // If checked and sendEmailAs has no value, skip save.
                if (getElementOnClickCheckedStatus(this.id) && $(emailBccSelector).val() == '') {
                    return;
                }

                ajaxSave(self);
            });
        }

        function setAutoEmailEvents(self)
        {
            $(document).on('click', autoEmailSettingsSelector, function () {
                emailInvoiceFields.toggleClass('hidden');

                // If checked and sendEmailAs has no value, skip save.
                if (getElementOnClickCheckedStatus(this.id) && $(emailSendAsSelector).val() == '') {
                    $(emailInvoiceNotificationSelector).removeClass('hidden');
                    return;
                }

                ajaxSave(self);
            });
        }

        function setEmailBccEvents(self)
        {
            var attempt, timer;

            $(document).on('keyup', emailBccSelector, function() {
                if (attempt) { attempt.abort() }
                clearTimeout(timer);
                timer = setTimeout(function() {
                    attempt = handleEmailBccKeyup(self);
                }, 1000)
            });
        }

        function handleEmailBccKeyup(self)
        {
            var email = $(emailBccSelector).val();
            if (validateEmail(email) || email == '') {
                ajaxSave(self)
            }
        }

        function getElementOnClickCheckedStatus(elementID)
        {
            return $('#' + elementID).is(":checked");
        }

        function appendEmailVerifiedStatus(template, status)
        {
            status.id = 'emailVerifiedStatus';

            CGMustache.get().fetchTemplates(template, function (templates, cgmustache) {
                var emailVerifiedStatus = cgmustache.renderTemplate(templates, status, "status");
                $('.email-send-as-holder').append(emailVerifiedStatus);
            });
        }

        function validateEmail(email)
        {
            return (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email));
        }

        this.getInvoiceSettingsEntity = function ()
        {
            return {
                'default': getDefault(),
                'autoEmail': getAutoEmail(),
                'emailSendAs': getEmailSendAs(),
                'copyRequired': getCopyRequired(),
                'emailBcc': getEmailBcc(),
                'productImages': getProductImages(),
                'tradingCompanies': getTradingCompanies(),
                'eTag': $('#setting-etag').val()
            };
        };

        this.handleSaveResponse = function (data)
        {
            $('#setting-etag').val(data.eTag);
            if (n) {
                n.success(InvoiceSettings.SUCCESS_MESSAGE);
            }
        };

        this.handleVerifyResponse = function (data)
        {
            $('#setting-etag').val(data.eTag);

            if (data.emailVerifiedStatus) {
                emailVerifyButton.addClass('hidden');

                appendEmailVerifiedStatus({
                    status: '/cg-built/zf2-v4-ui/templates/elements/status.mustache'
                }, data.emailVerifiedStatus);

                if (data.emailVerifiedStatus.status == InvoiceSettings.EMAIL_STATUS_PENDING) {
                    showConfirmationMessage();
                    $(emailInvoiceNotificationSelector).removeClass('hidden');
                }

                if (data.emailVerifiedStatus.status == InvoiceSettings.EMAIL_STATUS_VERIFIED) {
                    $(emailInvoiceNotificationSelector).addClass('hidden');
                }
            }

            emailVerifyButton.prop('disabled', false).removeClass('verifying').text('Verify');
        };

        var getDefault = function()
        {
            return $(defaultSettingsSelector).val();
        };

        var getAutoEmail = function()
        {
            return $(autoEmailSettingsSelector).is(':checked');
        };

        var getEmailSendAs = function()
        {
            return $(emailSendAsSelector).val();
        };

        var getCopyRequired = function()
        {
            return $(copyRequiredSelector).is(':checked');
        };

        var getEmailBcc = function()
        {
            return $(emailBccSelector).val();
        };

        var getProductImages = function()
        {
            return $(productImagesSettingsSelector).is(':checked');
        };

        var getTradingCompanies = function()
        {
            var tradingCompanies = {};

            $(tradingCompaniesAssignedInvoiceSelector).each(function() {
                var assignedInvoice = $(this).val();
                var tradingCompanyId = $(this).attr('name').replace('invoiceTradingCompaniesCustomSelect_', '');
                tradingCompanies[tradingCompanyId] = {'assignedInvoice': assignedInvoice};
            });

            $(tradingCompaniesSendFromAddressSelector).each(function() {
                var emailSendAs = $(this).val();
                var tradingCompanyId = $(this).attr('name').replace('invoiceSendFromAddressInput_', '');
               tradingCompanies[tradingCompanyId]['emailSendAs'] = emailSendAs;
            });

            return tradingCompanies;
        };

        init.call(this);
    };

    InvoiceSettings.POPUP_WIDTH_PX = 400;
    InvoiceSettings.POPUP_HEIGHT_PX = 'auto';
    InvoiceSettings.SUCCESS_MESSAGE = 'Settings Saved';
    InvoiceSettings.EMAIL_STATUS_VERIFIED = 'active';
    InvoiceSettings.EMAIL_STATUS_PENDING = 'pending';

    InvoiceSettings.prototype.save = function(callback)
    {
        var self = this;
        $.ajax({
            url: "mapping/save",
            type: "POST",
            dataType: 'json',
            data: self.getInvoiceSettingsEntity()
        }).success(function(data) {
            callback(data);
        }).error(function(error, textStatus, errorThrown) {
            if (n) {
                n.ajaxError(error, textStatus, errorThrown);
            }
        });
    };

    return InvoiceSettings
});
