define([
    'react',
    'redux-form',
    'Common/Components/ChannelBadge',
], function(
    React,
    ReduxForm,
    ChannelBadgeComponent
) {
    "use strict";

    var Field = ReduxForm.Field;

    var channelsWithDefaultSettings = {
        'ebay': true
    };

    var AccountSelectComponent = React.createClass({
        getDefaultProps: function () {
            return {
                accounts: {},
                accountSettings: {},
                fetchSettingsForAccount: function() {},
                touch: function() {},
            }
        },
        renderAccountBadge: function(field) {
            var accountData = field.account;
            return (
                <span className="channel-badge-container">
                    <ChannelBadgeComponent
                        id={accountData.id}
                        channel={accountData.channel}
                        displayName={accountData.name}
                        onClick={this.onAccountSelected.bind(this, field.input, accountData)}
                        selected={!!field.input.value}
                    />
                    {this.renderErrorMessage(field.meta)}
                </span>
            );
        },
        onAccountSelected: function(input, accountData, accountId) {
            if (this.shouldFetchSettingsForAccount(accountId, accountData.channel)) {
                this.props.fetchSettingsForAccount(accountId);
            }
            input.onChange(input.value ? null : accountId);
        },
        shouldFetchSettingsForAccount(accountId, channel) {
            if (!(channel in channelsWithDefaultSettings)) {
                return false;
            }
            if (accountId in this.props.accountSettings) {
                return false;
            }
            return true;
        },
        renderErrorMessage: function(meta) {
            if (!meta.error) {
                return null;
            }
            var error = JSON.parse(meta.error);
            return <span className="input-error account-error">
                {error.message}
                {this.renderErrorLink(error)}
            </span>;
        },
        renderErrorLink: function(error) {
            if (!error.linkTitle || !error.linkUrl) {
                return null;
            }
            return <a href={error.linkUrl} target={"_blank"}>
                {error.linkTitle}
            </a>;
        },
        renderGeneralErrorMessage: function() {
            var meta = this.props.meta;
            return meta.invalid && meta.error && (
                <span className="input-error accounts-error">
                    {meta.error}
                </span>
            );
        },
        render: function() {
            var accountSelects = [],
                index = 0;
            for (var accountId in this.props.accounts) {
                var account = this.props.accounts[accountId];
                accountSelects.push(
                    <Field
                        name={"accounts." + index}
                        component={this.renderAccountBadge}
                        account={account}
                    />
                );
                index++;
            }
            return (<span className="form-input-container">
                {accountSelects}
                {this.renderGeneralErrorMessage()}
            </span>);
        }
    });

    return AccountSelectComponent;
});