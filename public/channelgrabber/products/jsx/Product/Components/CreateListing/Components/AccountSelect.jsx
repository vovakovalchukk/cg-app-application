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

    var AccountSelectComponent = React.createClass({
        getDefaultProps: function () {
            return {
                accounts: {}
            }
        },
        renderAccountBadge: function(accountData, field) {
            return (
                <span className="channel-badge-container">
                    <ChannelBadgeComponent
                        id={accountData.id}
                        channel={accountData.channel}
                        displayName={accountData.name}
                        onClick={this.onAccountSelected.bind(this, field.input)}
                        selected={!!field.input.value}
                    />
                    {this.renderErrorMessage(field.meta)}
                </span>
            );
        },
        onAccountSelected: function(input, accountId) {
            input.onChange(input.value ? null : accountId);
        },
        renderErrorMessage: function(meta) {
            if (!meta.error) {
                return null;
            }
            return <span className="input-error account-error">{meta.error}</span>;
        },
        render: function() {
            var accountSelects = [],
                index = 0;
            for (var accountId in this.props.accounts) {
                var account = this.props.accounts[accountId];
                accountSelects.push(
                    <Field
                        name={"accounts." + index}
                        component={this.renderAccountBadge.bind(this, account)}
                    />
                );
                index++;
            }
            return (<span className="form-input-container">
                {accountSelects}
            </span>);
        }
    });

    return AccountSelectComponent;
});