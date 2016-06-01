define([
    '../SetupWizard.js',
    'cg-mustache',
    'popup/generic',
    'popup/confirm',
    'AjaxRequester'
], function(
    setupWizard,
    CGMustache,
    Popup,
    Confirm,
    ajaxRequester
) {
    function Channels(
        notifications,
        pickChannelUri,
        saveAccountUri,
        deleteAccountUri,
        editPopupTemplateUri,
        buttonsTemplateUri
    ) {
        var editPopupTemplates;

        this.getSetupWizard = function()
        {
            return setupWizard;
        };

        this.getNotifications = function()
        {
            return notifications;
        };

        this.getAjaxRequester = function()
        {
            return ajaxRequester;
        };

        this.getPickChannelUri = function()
        {
            return pickChannelUri;
        };

        this.getSaveAccountUri = function()
        {
            return saveAccountUri;
        };

        this.getDeleteAccountUri = function()
        {
            return deleteAccountUri;
        };

        this.getEditPopupTemplateUri = function()
        {
            return editPopupTemplateUri;
        };

        this.getButtonsTemplateUri = function()
        {
            return buttonsTemplateUri;
        };

        this.getEditPopupTemplates = function()
        {
            return editPopupTemplates;
        };

        this.setEditPopupTemplates = function(templates)
        {
            editPopupTemplates = templates;
            return this;
        };

        var init = function()
        {
            this.registerNextValidation()
                .hideSkipIfAccountsPresent()
                .listenForAddClick()
                .listenForEditClick()
                .listenForDeleteClick();
        };
        init.call(this);
    }

    Channels.SELECTOR_CHANNEL = '.setup-wizard-account-badge';
    Channels.SELECTOR_CHANNEL_ID = '#setup-wizard-account-badge-';
    Channels.SELECTOR_ACCOUNT_NAME = '.setup-wizard-account-badge-name';
    Channels.SELECTOR_SKIP = '.setup-wizard-skip-button';
    Channels.SELECTOR_ADD = '.setup-wizard-account-badges .setup-wizard-button-badge';
    Channels.SELECTOR_EDIT = '.setup-wizard-account-edit';
    Channels.SELECTOR_DELETE = '.setup-wizard-account-delete';
    Channels.SELECTOR_EDIT_SAVE = '#setup-wizard-edit-save-button';
    Channels.SELECTOR_EDIT_CANCEL = '#setup-wizard-edit-cancel-button';

    Channels.prototype.registerNextValidation = function()
    {
        this.getSetupWizard().registerNextCallback(function()
        {
            if ($(Channels.SELECTOR_CHANNEL).length == 0) {
                n.error('You must add at least one channel (or you can choose to skip this step)');
                return false;
            }
            return true;
        });

        return this;
    };

    Channels.prototype.hideSkipIfAccountsPresent = function()
    {
        if ($(Channels.SELECTOR_CHANNEL).length == 0) {
            return this;
        }
        // If the user has added accounts then skipping doesnt make sense
        $(Channels.SELECTOR_SKIP).hide();
        return this;
    };

    Channels.prototype.showSkipIfNoAccountsPresent = function()
    {
        if ($(Channels.SELECTOR_CHANNEL).length > 0) {
            return this;
        }
        // If there's no accounts then the user can skip if they wish
        $(Channels.SELECTOR_SKIP).show();
        return this;
    };

    Channels.prototype.listenForAddClick = function()
    {
        var self = this;
        $(Channels.SELECTOR_ADD).click(function()
        {
            self.showNewChannelOptions();
        });

        return this;
    };

    Channels.prototype.showNewChannelOptions = function()
    {
        window.location = this.getPickChannelUri();
    };

    Channels.prototype.listenForEditClick = function()
    {
        var self = this;
        $(Channels.SELECTOR_EDIT).click(function(event)
        {
            event.preventDefault(); 
            var anchor = this;
            var badge = $(anchor).closest(Channels.SELECTOR_CHANNEL)
            var accountId = badge.data('account');
            var name = badge.find(Channels.SELECTOR_ACCOUNT_NAME).text();

            self.editAccount(accountId, name);
        });

        return this;
    };

    Channels.prototype.editAccount = function(accountId, name)
    {
        var templateMap = {
            "buttons": this.getButtonsTemplateUri(),
            "editPopup": this.getEditPopupTemplateUri()
        };
        var self = this;
        this.fetchEditPopupTemplates(templateMap, function(templates, cgMustache)
        {
            var popup = self.renderAndShowEditPopup(templates, cgMustache, accountId, name);
            self.listenForEditPopupButtonClicks(popup);
        });
    };

    Channels.prototype.fetchEditPopupTemplates = function(templateMap, callback)
    {
        if (this.getEditPopupTemplates()) {
            callback(this.getEditPopupTemplates(), CGMustache.get());
            return;
        }
        var self = this;
        CGMustache.get().fetchTemplates(templateMap, function(templates, cgMustache)
        {
            self.setEditPopupTemplates(templates);
            callback(templates, cgMustache);
        });
    };

    Channels.prototype.renderAndShowEditPopup = function(templates, cgMustache, accountId, name)
    {
        var buttons = cgMustache.renderTemplate(templates, {
            "buttons": [{
                "id": "setup-wizard-edit-save-button",
                "value": "Save"
            }, {
                "id": "setup-wizard-edit-cancel-button",
                "value": "Cancel"
            }]
        }, "buttons");

        var editPopup = cgMustache.renderTemplate(templates, {
            "accountId": accountId,
            "name": name
        }, "editPopup", {"buttons": buttons});

        var popup = new Popup(editPopup);
        popup.show();

        return popup;
    };

    Channels.prototype.listenForEditPopupButtonClicks = function(popup)
    {
        var self = this;
        $(Channels.SELECTOR_EDIT_SAVE).off('click.setup-wizard-channels')
            .on('click.setup-wizard-channels', function()
            {
                var accountId = popup.getElement().find('input[name="id"]').val();
                var name = popup.getElement().find('input[name="displayName"]').val();

                self.closePopup(popup)
                    .saveAccountName(accountId, name);
            });

        $(Channels.SELECTOR_EDIT_CANCEL).off('click.setup-wizard-channels')
            .on('click.setup-wizard-channels', function()
            {
                self.closePopup(popup);
            });
    };

    Channels.prototype.closePopup = function(popup)
    {
        popup.hide();
        popup.getElement().remove();
        delete popup;
        return this;
    };

    Channels.prototype.saveAccountName = function(id, name)
    {
        var self = this;
        this.getNotifications().notice('Saving channel name');
        this.getAjaxRequester().sendRequest(this.getSaveAccountUri(), {"id": id, "displayName": name}, function()
        {
            self.getNotifications().success('Changes saved successfully');
            $(Channels.SELECTOR_CHANNEL_ID + id + ' ' + Channels.SELECTOR_ACCOUNT_NAME).text(name);
        });
    };

    Channels.prototype.listenForDeleteClick = function()
    {
        var self = this;
        $(Channels.SELECTOR_DELETE).click(function(event)
        {
            event.preventDefault();
            var anchor = this;
            var badge = $(anchor).closest(Channels.SELECTOR_CHANNEL)
            var accountId = badge.data('account');
            var name = badge.find(Channels.SELECTOR_ACCOUNT_NAME).text();

            self.deleteAccountWithConfirmation(accountId, name);
        });

        return this;
    };

    Channels.prototype.deleteAccountWithConfirmation = function(accountId, name)
    {
        var self = this;
        var message = 'Are you sure you wish to delete <strong>' + name + '</strong>?'
        var confirm = new Confirm(message, function(answer)
        {
            if (answer !== Confirm.VALUE_YES) {
                return;
            }
            self.deleteAccount(accountId);
        });
        confirm.getElement().addClass('setup-wizard-popup');
    };

    Channels.prototype.deleteAccount = function(id)
    {
        var self = this;
        this.getNotifications().notice('Deleting channel');
        this.getAjaxRequester().sendRequest(this.getDeleteAccountUri(), {"id": id}, function()
        {
            self.getNotifications().success('Channel deleted successfully');
            $(Channels.SELECTOR_CHANNEL_ID + id).remove();
            self.showSkipIfNoAccountsPresent();
        });
    };

    return Channels;
});