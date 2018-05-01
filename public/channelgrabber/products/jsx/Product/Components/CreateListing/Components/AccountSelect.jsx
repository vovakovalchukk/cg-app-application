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
                fetchSettingsForAccount: function() {}
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
            var newValue = input.value ? null : accountId;
            if (newValue && accountData.channel in channelsWithDefaultSettings) {
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
            console.log(meta.error, $.parseHTML(meta.error));
            return <span className="input-error account-error">{meta.error}</span>;
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