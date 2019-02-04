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
        var InvoiceSettings = function(basePath, amazonSite, tagOptions) {

            var container = '.invoiceSettings';
            var selector = container + ' .custom-select, #itemSku, #productImages, #itemBarcodes, #itemVariationAttributes';
            var itemSkuSettingsSelector = container + ' .invoiceDefaultSettings #itemSku';
            var productImagesSettingsSelector = container + ' .invoiceDefaultSettings #productImages';
            var itemBarcodesSettingsSelector = container + ' .invoiceDefaultSettings #itemBarcodes';
            var itemVariationAttributesSettingsSelector = container + ' .invoiceDefaultSettings #itemVariationAttributes';
            var tradingCompaniesAssignedInvoiceSelector = container + ' .invoiceTradingCompanySettings input.invoiceTradingCompaniesCustomSelect';
            var tradingCompaniesSendFromAddressSelector = container + ' .invoiceTradingCompanySettings input.invoiceSendFromAddressInput';

            var invoiceSendFromAddressColumnHeadSelector = '#accounts_wrapper .dataTable thead tr th:nth-child(3)';
            var invoiceSendFromAddressColumnSelector = '#accounts_wrapper #accounts tbody tr td:nth-child(3)';

            var defaultSettingsSelector = '#defaultInvoiceCustomSelect input';
            var autoEmailSettingsSelector = '.invoiceMappingSettings #autoEmail';
            var sendToFbaDefaultSelector = '.invoiceMappingSettings #sendToFbaDefault';
            var emailSendAsSelector = '.invoiceMappingSettings #emailSendAs';
            var copyRequiredSelector = '.invoiceMappingSettings #copyRequired';
            var emailBccSelector = '.invoiceMappingSettings #emailBcc';

            var mappingSelector = '.invoiceMapping .invoiceMappingTable';
            var mappingTradingCompanySelector = '.trading-company-column';
            var mappingAssignedInvoiceSelector = '.assigned-invoice-column';
            var mappingSendViaEmailSelector = '.send-via-email-column';
            var mappingSendToFbaSelector = '.send-to-fba-column';

            var invoiceSettingsField = '.invoice-settings-field';

            var emailVerifyInputSelector = '.email-verify-input';
            var emailVerifyButtonSelector = '.email-verify-button';
            var emailVerifyStatusSelector = '.email-verify-status';
            var emailVerifyHolderSelector = '.email-send-as-holder';
            var emailEditTemplateButtonSelector = '.edit-content-button';
            var isPendingConfirmationMessageRequired = false;

            var emailInvoiceFieldsSelector = container + ' .emailInvoiceFields';
            var emailInvoiceNotificationSelector = '#emailInvoiceNotification';

            var emailBccField = $(emailBccSelector);

            var emailEditorContentId = 'invoice-email-editor';
            var emailSubjectEditorId = 'invoice-email-subject-editor';
            var emailEditorSelector = '#' + emailEditorContentId;
            var emailSubjectEditorSelector = '#' + emailSubjectEditorId;
            var existingEmailTemplate = $(emailEditorSelector).html();
            EventCollator.setTimeout(3000);

            var init = function ()
            {
                var attempt, timer;
                var self = this;

                // Set field states
                setCopyRequired();

                // Set event listeners
                $(document).on('change', selector, function() {
                    ajaxSave(self);
                });

                $(document).on('change', invoiceSettingsField, function() {
                    self.save(handleSaveResponse);
                });

                $(document).on('change', mappingTradingCompanySelector, function(event, element) {
                    var saveData = self.getMappingSaveData(element, 'organisationUnitId');
                    self.saveMapping(saveData, handleSaveMappingResponse);
                });

                $(document).on('change', mappingAssignedInvoiceSelector, function(event, element) {
                    var saveData = self.getMappingSaveData(element, 'invoiceId');
                    self.saveMapping(saveData, handleSaveMappingResponse);
                });

                $(document).on('change', mappingSendViaEmailSelector, function(event) {
                    let element = event.target;
                    let saveData = Object.assign(buildSaveMappingBaseData($(element)), {
                        sendViaEmail: $(element).is(':checked')
                    });
                    self.saveMapping(saveData, handleSaveMappingResponse);
                });

                $(document).on('change', mappingSendToFbaSelector, function(event) {
                    let element = event.target;
                    let saveData = Object.assign(buildSaveMappingBaseData($(element)), {
                        sendToFba: $(element).is(':checked')
                    });
                    self.saveMapping(saveData, handleSaveMappingResponse);
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

                    if (amazonSite !== '') {
                        showConfirmationMessageForAmazonAccount(self, amazonSite, $(this));
                    } else {
                        ajaxVerify(self);
                    }
                });

                $(document).on('click', emailEditTemplateButtonSelector, function (event) {
                    renderEmailTemplatePopup(event.target, function(saveData) {
                        self.saveMapping(saveData, function(data) {
                            handleInvoiceMappingSuccessSave(data, saveData, $(event.target));
                        });
                    });
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

            function handleInvoiceMappingSuccessSave(responseData, savedData, element) {
                handleSaveMappingResponse(responseData);
                element.data({
                    subject: savedData.emailSubject,
                    content: savedData.emailTemplate
                });
            }

            function renderEmailTemplatePopup(editButton, saveMappingCallback) {
                var emailData = $(editButton).data();

                var templateUrlMap = {
                    invoiceEmail: '/cg-built/settings/template/Messages/invoiceEmailEditForm.mustache'
                };

                CGMustache.get().fetchTemplates(
                    templateUrlMap,
                    function (templates, cgmustache) {
                        var messageHTML = cgmustache.renderTemplate(templates, emailData, "invoiceEmail");
                        renderEmailTemplatePopupContent(editButton, messageHTML, saveMappingCallback);
                    }
                );
            }

            function renderEmailTemplatePopupContent(editButton, messageHTML, saveMappingCallback) {
                new Confirm(
                    messageHTML,
                    (response) => { handleEmailTemplatePopupAction(editButton, response, saveMappingCallback) },
                    buildEmailTemplatePopupButtons(),
                    () => {},
                    buildEmailTemplatePopupName(editButton)
                );
            }

            function handleEmailTemplatePopupAction(editButton, response, saveMappingCallback) {
                if (!response) {
                    setupEmailEditor();
                    setupEmailSubjectEditor();
                    return;
                }

                if (response == 'Save') {
                    let saveData = Object.assign(buildSaveMappingBaseData($(editButton)), {
                        emailSubject: tinyMCE.get(emailSubjectEditorId).getContent(),
                        emailTemplate: tinyMCE.get(emailEditorContentId).getContent()
                    });
                    saveMappingCallback(saveData);
                }

                tinyMCE.remove();
            }

            function buildEmailTemplatePopupButtons() {
                return [
                    {
                        value: 'Cancel'
                    },
                    {
                        value: 'Save',
                        class: 'yes-button'
                    }
                ];
            }

            function buildEmailTemplatePopupName(editButton) {
                let row = $(editButton).closest('tr');
                let accountName = $.trim($(row.find('.account-column')).text());
                let site = $.trim($(row.find('.site-column input')).val());
                return 'Email Subject and Content for ' + accountName + ' ' + site;
            }

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

            function showConfirmationMessageForAmazonAccount(self, amazonSite, emailVerifyButton)
            {
                var templateUrlMap = {
                    message: '/cg-built/settings/template/Warnings/amazonEmailWarning.mustache'
                };

                CGMustache.get().fetchTemplates(templateUrlMap, function (templates, cgmustache) {
                    var messageHTML = cgmustache.renderTemplate(templates, {'basePath': basePath, 'amazonSite': amazonSite}, "message");
                    new Confirm(messageHTML, function (response) {
                        if (response == InvoiceSettings.EMAIL_VALIDATION_CONFIRMATION_AMAZON) {
                            setEmailVerifyButtonVerifying(emailVerifyButton);
                            ajaxVerify(self, {'confirmationAmazon': true});
                        }
                    }, ["Cancel", {
                        value: InvoiceSettings.EMAIL_VALIDATION_CONFIRMATION_AMAZON,
                        class: 'yes-button'
                    }]);
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

            function ajaxVerify(object, additionalData)
            {
                object.save(handleVerifyResponse, additionalData || {});
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

            function setupEmailSubjectEditor() {
                var tags = JSON.parse(tagOptions);

                tinyMCE.init({
                    selector: emailSubjectEditorSelector,
                    theme_url: '/channelgrabber/zf2-v4-ui/js/jqueryPlugin/tinymce/theme.js',
                    skin_url: '/channelgrabber/zf2-v4-ui/js/jqueryPlugin/tinymce/orderhub',
                    plugins: ['save', 'textcolor'],
                    height: 10,
                    statusbar : false,
                    menubar : false,
                    forced_root_block: false,
                    paste_as_text: true,
                    toolbar: 'tagSelect',
                    inline: true,
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
                        editor.on('keydown', function (event) {
                            if (event.keyCode == 13)  {
                                event.preventDefault();
                                event.stopPropagation();
                                return false;
                            }
                        })
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

            function handleSaveMappingResponse(data)
            {
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
                    'sendToFba': getSendToFba(),
                    'emailSendAs': getEmailSendAs(),
                    'copyRequired': getCopyRequired(),
                    'emailBcc': getEmailBcc(),
                    'emailTemplate': getEmailTemplate(),
                    'itemSku': getItemSku(),
                    'productImages': getProductImages(),
                    'itemBarcodes': getItemBarcodes(),
                    'itemVariationAttributes': getItemVariationAttributes(),
                    'tradingCompanies': getTradingCompanies(),
                    'eTag': $('#setting-etag').val()
                };
            };

            this.getMappingSaveData = function (element, property) {
                var saveData = buildSaveMappingBaseData(element);
                saveData[property] = $(element.closest('td').find('.custom-select input')[0]).val();
                return saveData;
            };

            function buildSaveMappingBaseData(element) {
                var row = element.closest('tr');
                return {
                    id: $(row).attr('data-element-row-id'),
                    site: $.trim($(row.find('.site-column input')).val()),
                    accountId: $(row.find('.account-column input')).val(),
                };
            }

            this.initialiseInvoiceMappingEntity = function (entity) {
                $('#invoiceMapping tbody tr').each(function (index, row) {
                    var rowId = $(row).attr('data-element-row-id');
                    if(typeof rowId === typeof undefined || rowId === false) {
                        $(row).attr('data-element-row-id', entity.id);
                    }
                });
            };

            var getDefault = function()
            {
                return $(defaultSettingsSelector).val();
            };

            var getAutoEmail = function()
            {
                return $(autoEmailSettingsSelector).is(':checked');
            };

            var getSendToFba = function ()
            {
                return $(sendToFbaDefaultSelector).is(':checked');
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

            var getItemVariationAttributes = function()
            {
                return $(itemVariationAttributesSettingsSelector).is(':checked');
            };

            var getTradingCompanies = function()
            {
                var tradingCompanies = {};

                $(tradingCompaniesSendFromAddressSelector).each(function() {
                    var emailSendAs = $(this).val();
                    var tradingCompanyId = $(this).attr('name').replace('invoiceSendFromAddressInput_', '');
                    tradingCompanies[tradingCompanyId] = {'emailSendAs': emailSendAs};
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
        InvoiceSettings.EMAIL_VALIDATION_CONFIRMATION_AMAZON = 'Confirm';

        InvoiceSettings.prototype.save = function(callback, additionalData)
        {
            var self = this;
            $.ajax({
                url: "settings/save",
                type: "POST",
                dataType: 'json',
                data: $.extend({}, self.getInvoiceSettingsEntity(), additionalData || {})
            }).success(function(data) {
                callback(data);
            }).error(function(error, textStatus, errorThrown) {
                if (n) {
                    n.ajaxError(error, textStatus, errorThrown);
                }
            });
        };

        InvoiceSettings.prototype.saveMapping = function(saveData, callback)
        {
            var self = this;
            $.ajax({
                url: "settings/saveMapping",
                type: "POST",
                dataType: 'json',
                data: saveData
            }).success(function(data) {
                self.initialiseInvoiceMappingEntity(JSON.parse(data.invoiceMapping));
                callback(data);
            }).error(function(error, textStatus, errorThrown) {
                if (n) {
                    n.ajaxError(error, textStatus, errorThrown);
                }
            });
        };

        return InvoiceSettings
    });
