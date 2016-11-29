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
        var invoiceSendFromAddressColumnHeadSelector = '#accounts_wrapper .dataTable thead tr th:nth-child(3)';
        var invoiceSendFromAddressColumnSelector = '#accounts_wrapper #accounts tbody tr td:nth-child(3)';

        var emailVerifyInputSelector = '.email-verify-input';
        var emailVerifyButtonSelector = '.email-verify-button';
        var emailVerifyStatusSelector = '.email-verify-status';
        var emailVerifyHolderSelector = '.email-send-as-holder';
        var isPendingConfirmationMessageRequired = false;

        var emailVerifiedStatusSelector = '#emailVerifiedStatus';
        var emailInvoiceFieldsSelector = container + ' .emailInvoiceFields';
        var emailInvoiceNotificationSelector = '#emailInvoiceNotification';

        var emailBccField = $(emailBccSelector);
        var emailVerifiedStatus = $(emailVerifiedStatusSelector);

        var init = function ()
        {
            var attempt, timer;
            var self = this;

            // Set field states
            setAutoEmail();
            setCopyRequired();

            // Set event listeners
            $(document).on('change', selector, function() {
                ajaxSave(self);
            });

            $(document).on('change keyup', emailVerifyInputSelector, function () {
                $(this).siblings(emailVerifyButtonSelector).removeClass('hidden');
                $(this).siblings(emailVerifyStatusSelector).remove();
            });

            $(document).on('click', emailVerifyButtonSelector, function (e) {
                e.preventDefault();

                if (getElementOnClickCheckedStatus('autoEmail') && hasAmazonAccount == true) {
                    showConfirmationMessageForAmazonAccount(self, $(this));
                } else {
                    ajaxVerify(self);
                }
            });

            $(document).on('change', copyRequiredSelector, function () {
                emailBccField.toggle();

                // If checked and sendEmailAs has no value, skip save.
                if (getElementOnClickCheckedStatus(this.id) && $(emailBccSelector).val() == '') {
                    return;
                }

                ajaxSave(self);
            });

            $(document).on('click', autoEmailSettingsSelector, function () {
                $(emailInvoiceFieldsSelector).toggleClass('hidden');
                $(invoiceSendFromAddressColumnHeadSelector).toggle();
                $(invoiceSendFromAddressColumnSelector).toggle();

                // If checked and sendEmailAs has no value, skip save.
                if (getElementOnClickCheckedStatus(this.id) && $(emailSendAsSelector).val() == '') {
                    $(emailInvoiceNotificationSelector).removeClass('hidden');
                    return;
                }

                ajaxSave(self);
            });

            $(document).on('keyup', emailBccSelector, function() {
                if (attempt) { attempt.abort() }
                clearTimeout(timer);
                timer = setTimeout(function() {
                    attempt = handleEmailBccKeyup(self);
                }, 1000)
            });

            $(document).on('fnDrawCallback', function() {
                if (! $('#autoEmail').prop('checked')) {
                    hideSendFromAddressColumn();
                }
            });
        };

        function showConfirmationMessageForAmazonAccount(self, emailVerifyButton)
        {
            var templateUrlMap = {
                message: '/cg-built/settings/template/Warnings/amazonEmailWarning.mustache'
            };

            CGMustache.get().fetchTemplates(templateUrlMap, function (templates, cgmustache) {
                var messageHTML = cgmustache.renderTemplate(templates, {}, "message");
                new Confirm(messageHTML, function (response) {
                    if (response == "Yes") {
                        setEmailVerifyButtonVerifying(emailVerifyButton);
                        ajaxVerify(self);
                    }
                });
            });
        }

        function showPendingConfirmationMessage()
        {
            var templateUrlMap = {
                message: '/cg-built/settings/template/Messages/emailInvoiceConfirmationMessage.mustache'
            };

            CGMustache.get().fetchTemplates(templateUrlMap, function (templates, cgmustache) {
                var messageHTML = cgmustache.renderTemplate(templates, {}, "message");
                new Confirm(messageHTML, function () {
                    isPendingConfirmationMessageRequired = false;
                }, ['Ok']);
            });
        }

        function ajaxSave(object)
        {
            object.save(handleSaveResponse);
        }

        function ajaxVerify(object)
        {
            object.save(handleVerifyResponse);
        }

        function setAutoEmail()
        {
            if ($('#autoEmail').prop('checked')) {
                $(emailInvoiceFieldsSelector).removeClass('hidden');
            } else {
                hideSendFromAddressColumn();
            }
        }

        function setCopyRequired()
        {
            if (!$('#copyRequired').prop('checked')) {
                emailBccField.hide();
            }
        }

        function setEmailVerifyButtonVerifying(emailVerifyButton)
        {
            emailVerifyButton.prop('disabled', true).addClass('verifying').text('Verifying...');
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

        function refreshEmailVerifiedStatus(status, target)
        {
            CGMustache.get().fetchTemplates('/cg-built/zf2-v4-ui/templates/elements/status.mustache', function (templates, cgmustache) {
                if (target.children('.status').length) {
                    target.children('.status').replaceWith(cgmustache.renderTemplate(templates, status, "status"))
                } else {
                    target.append(cgmustache.renderTemplate(templates, status, "status"));
                }
            });
        }

        function validateEmail(email)
        {
            return (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email));
        }

        function handleSaveResponse(data)
        {
            $('#setting-etag').val(data.eTag);
            if (n) {
                n.success(InvoiceSettings.SUCCESS_MESSAGE);
            }
        }

        function handleVerifyResponse(data)
        {
            $('#setting-etag').val(data.eTag);

            $.each(data.emailVerifiedStatus, function(index, status) {
                updateEmailVerifiedStatusForId(index, status);
            });

            if (isPendingConfirmationMessageRequired) {
                showPendingConfirmationMessage();
            }
        }

        function updateEmailVerifiedStatusForId(id, emailVerifiedStatus)
        {
            var emailVerifyButton = $(emailVerifyButtonSelector + '[data-id='+id+']')
                .addClass('hidden')
                .prop('disabled', false)
                .removeClass('verifying')
                .text('Verify');
            var target = emailVerifyButton.parent(emailVerifyHolderSelector);

            refreshEmailVerifiedStatus(emailVerifiedStatus, target);

            if (emailVerifiedStatus.status == InvoiceSettings.EMAIL_STATUS_PENDING) {
                isPendingConfirmationMessageRequired = true;
                $(emailInvoiceNotificationSelector).removeClass('hidden');
            }

            if (emailVerifiedStatus.status == InvoiceSettings.EMAIL_STATUS_VERIFIED) {
                $(emailInvoiceNotificationSelector).addClass('hidden');
            }
        }

        function hideSendFromAddressColumn()
        {
            $(invoiceSendFromAddressColumnHeadSelector).hide();
            $(invoiceSendFromAddressColumnSelector).hide();
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
