define([
        "popup/confirm",
        "cg-mustache",
        'tinyMCE',
        'EventCollator'
    ], function (
        Confirm,
        CGMustache,
        tinyMCE,
        EventCollator
){
        var InvoiceSettings = function(hasAmazonAccount, tagOptions) {

            var container = '.invoiceSettings';
            var selector = container + ' .custom-select, #itemSku, #productImages, #itemBarcodes';
            var defaultSettingsSelector = container + ' .invoiceDefaultSettings #defaultInvoiceCustomSelect input';
            var autoEmailSettingsSelector = container + ' .invoiceDefaultSettings #autoEmail';
            var itemSkuSettingsSelector = container + ' .invoiceDefaultSettings #itemSku';
            var productImagesSettingsSelector = container + ' .invoiceDefaultSettings #productImages';
            var itemBarcodesSettingsSelector = container + ' .invoiceDefaultSettings #itemBarcodes';
            var tradingCompaniesAssignedInvoiceSelector = container + ' .invoiceTradingCompanySettings input.invoiceTradingCompaniesCustomSelect';
            var tradingCompaniesSendFromAddressSelector = container + ' .invoiceTradingCompanySettings input.invoiceSendFromAddressInput';
            var copyRequiredSelector = container + ' .invoiceDefaultSettings #copyRequired';
            var emailSendAsSelector = container + ' .invoiceDefaultSettings #emailSendAs';
            var emailBccSelector = container + ' .invoiceDefaultSettings #emailBcc';
            var invoiceSendFromAddressColumnHeadSelector = '#accounts_wrapper .dataTable thead tr th:nth-child(3)';
            var invoiceSendFromAddressColumnSelector = '#accounts_wrapper #accounts tbody tr td:nth-child(3)';

            var mappingSelector = '.invoiceMapping .invoiceMappingTable';
            var mappingTradingCompanySelector = '.invoiceMapping .trading-company-column';
            var mappingAssignedInvoiceSelector = '.invoiceMapping .assigned-invoice-column';
            var mappingSendViaEmailSelector = '.invoiceMapping .send-via-email-column';
            var mappingSendToFbaSelector = '.invoiceMapping .send-to-fba-column';

            var emailVerifyInputSelector = '.email-verify-input';
            var emailVerifyButtonSelector = '.email-verify-button';
            var emailVerifyStatusSelector = '.email-verify-status';
            var emailVerifyHolderSelector = '.email-send-as-holder';
            var isPendingConfirmationMessageRequired = false;

            var emailInvoiceFieldsSelector = container + ' .emailInvoiceFields';
            var emailInvoiceNotificationSelector = '#emailInvoiceNotification';

            var emailBccField = $(emailBccSelector);

            var emailEditorSelector = '#invoice-email-editor';
            var existingEmailTemplate = $(emailEditorSelector).html();
            EventCollator.setTimeout(3000);

            var init = function ()
            {
                var attempt, timer;
                var self = this;

                // Set field states
                setCopyRequired();

                // Setup emailEditor
                setupEmailEditor();

                // Set event listeners
                $(document).on('change', selector, function() {
                    ajaxSave(self);
                });

                $(document).on('change', mappingSelector, function(event, element) {
                    var invoiceMappingInput = $(element).closest('.assigned-invoice-column').find('input')[0];
                    var invoiceMappingId = $(invoiceMappingInput).attr('data-element-row-id');
                    self.saveMapping(invoiceMappingId, handleSaveResponse);
                });

                $(document).on('change keyup', emailVerifyInputSelector, function () {
                    $(this).siblings(emailVerifyButtonSelector).removeClass('hidden');
                    $(this).siblings(emailVerifyStatusSelector).remove();
                });

                $(document).on('click', emailVerifyButtonSelector, function (e) {
                    e.preventDefault();

                    if (! validateEmailFields()) {
                        n.error(InvoiceSettings.EMAIL_VALIDATION_FAILED);
                        return;
                    }

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
                    var emailVerifyInputFields = $(emailVerifyInputSelector);
                    var emailVerifyInputFieldsEmpty = true;

                    $.each(emailVerifyInputFields, function(index, el) {
                        if (el.value !== '') {
                            emailVerifyInputFieldsEmpty = false;
                        }
                    });

                    // If checked and all trading companies have no values, skip save.
                    if (getElementOnClickCheckedStatus(this.id) && emailVerifyInputFieldsEmpty) {
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

                $(document).on(EventCollator.getQueueTimeoutEventPrefix() + 'invoiceEmailTemplate', function(event, data) {
                    var newContent = $.trim(data.pop());
                    var oldContent = $.trim(existingEmailTemplate);

                    if (newContent === oldContent) {
                        return;
                    }
                    existingEmailTemplate = newContent;
                    ajaxSave(self);
                });
            };

            function validateEmailFields()
            {
                var valid = true;

                // If auto email is being switch off we can skip validation
                if (! getElementOnClickCheckedStatus('autoEmail')) {
                    return true;
                }

                var emailFields = $(emailVerifyInputSelector);
                $.each(emailFields, function(index, item) {
                    console.log(item.value.match(/no-reply@orderhub.io/gi));

                    if (item.value.match(/no-reply@orderhub.io/gi)) {
                        valid = false;
                    }
                });

                return valid;
            }

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

                $(emailInvoiceNotificationSelector).removeClass('hidden');
            }

            function ajaxSave(object)
            {
                if (! validateEmailFields()) {
                    n.error(InvoiceSettings.EMAIL_VALIDATION_FAILED);
                    return;
                }

                object.save(handleSaveResponse);
            }

            function ajaxVerify(object)
            {
                object.save(handleVerifyResponse);
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

            function setupEmailEditor() {
                var tags = JSON.parse(tagOptions);

                tinyMCE.init({
                    selector: emailEditorSelector,
                    theme_url: '/channelgrabber/zf2-v4-ui/js/jqueryPlugin/tinymce/theme.js',
                    skin_url: '/channelgrabber/zf2-v4-ui/js/jqueryPlugin/tinymce/orderhub',
                    plugins: ['save', 'textcolor'],
                    height: 200,
                    statusbar : false,
                    menubar : false,
                    forced_root_block: false,
                    paste_as_text: true,
                    init_instance_callback: function (editor) {
                        editor.on('keyup change paste SetContent', function (e) {
                            $(document).trigger(EventCollator.getRequestMadeEvent(), ['invoiceEmailTemplate', editor.getContent(), false]);
                        });
                    },
                    toolbar: 'fontselect | bold italic | fontsizeselect | forecolor | tagSelect | resetDefault',
                    setup: function(editor) {
                        function addToEditor(e){
                            tinymce.activeEditor.insertContent(e.target.textContent);
                        }
                        editor.addButton('resetDefault', {
                            text: 'Reset to Default',
                            onclick: function () {
                                tinymce.execCommand('mceCancel');
                            }
                        });
                        editor.addButton('tagSelect', {
                            type: 'menubutton',
                            text: 'Insert Tag',
                            icon: false,
                            menu: tags.map(function (tag) {
                                return {text: tag, onclick: addToEditor};
                            })
                        });

                    }
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
                $(emailVerifyButtonSelector).prop('disabled', false)
                    .removeClass('verifying')
                    .text('Verify');

                $.each(data.emailVerifiedStatus, function(index, status) {
                    updateEmailVerifiedStatusForId(index, status);
                });

                if (isPendingConfirmationMessageRequired) {
                    showPendingConfirmationMessage();
                }
            }

            function updateEmailVerifiedStatusForId(id, emailVerifiedStatus)
            {
                var emailVerifyButton = $(emailVerifyButtonSelector + '[data-id='+id+']').addClass('hidden');
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

            this.getInvoiceSettingsEntity = function ()
            {
                return {
                    'default': getDefault(),
                    'autoEmail': getAutoEmail(),
                    'emailSendAs': getEmailSendAs(),
                    'copyRequired': getCopyRequired(),
                    'emailBcc': getEmailBcc(),
                    'emailTemplate': getEmailTemplate(),
                    'itemSku': getItemSku(),
                    'productImages': getProductImages(),
                    'itemBarcodes': getItemBarcodes(),
                    'tradingCompanies': getTradingCompanies(),
                    'eTag': $('#setting-etag').val()
                };
            };

            this.getInvoiceMappingEntity = function (rowId) {
                var invoiceMappings = {};

                $(mappingTradingCompanySelector).each(function() {
                    var invoiceMappingId = $(this).find('input').attr('data-element-row-id');
                    if (rowId === invoiceMappingId) {
                        invoiceMappings[invoiceMappingId] = {'organisationUnitId': $(this).find('.custom-select input').val()};
                    }
                });
                $(mappingAssignedInvoiceSelector).each(function() {
                    var invoiceMappingId = $(this).find('input').attr('data-element-row-id');
                    if (rowId === invoiceMappingId) {
                        invoiceMappings[invoiceMappingId]['invoiceId'] = $(this).find('.custom-select input').val();
                    }
                });
                $(mappingSendViaEmailSelector).each(function() {
                    var invoiceMappingId = $(this).find('input').attr('data-element-row-id');
                    if (rowId === invoiceMappingId) {
                        invoiceMappings[invoiceMappingId]['sendViaEmail'] = $(this).find('.custom-select input').val();
                    }
                });
                $(mappingSendToFbaSelector).each(function() {
                    var invoiceMappingId = $(this).find('input').attr('data-element-row-id');
                    if (rowId === invoiceMappingId) {
                        invoiceMappings[invoiceMappingId]['sendToFba'] = $(this).find('.custom-select input').val();
                    }
                });
                return invoiceMappings;
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

            var getEmailTemplate = function()
            {
                return existingEmailTemplate;
            };

            var getItemSku = function()
            {
                return $(itemSkuSettingsSelector).is(':checked');
            };

            var getProductImages = function()
            {
                return $(productImagesSettingsSelector).is(':checked');
            };

            var getItemBarcodes = function()
            {
                return $(itemBarcodesSettingsSelector).is(':checked');
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
        InvoiceSettings.EMAIL_VALIDATION_FAILED = 'We no longer send email invoices using no-reply@orderhub.io. Update the Send From Email to an alternative email address before saving.';
        InvoiceSettings.EMAIL_STATUS_VERIFIED = 'success';
        InvoiceSettings.EMAIL_STATUS_PENDING = 'pending';
        InvoiceSettings.EMAIL_STATUS_FAILED = 'failed';

        InvoiceSettings.prototype.save = function(callback)
        {
            var self = this;
            $.ajax({
                url: "settings/save",
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

        InvoiceSettings.prototype.saveMapping = function(rowId, callback)
        {
            var self = this;
            $.ajax({
                url: "settings/saveMapping",
                type: "POST",
                dataType: 'json',
                data: self.getInvoiceMappingEntity(rowId)
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
