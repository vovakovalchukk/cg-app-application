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
            return <ChannelBadgeComponent
                id={accountData.id}
                channel={accountData.channel}
                displayName={accountData.name}
                onClick={this.onAccountSelected.bind(this, field.input)}
                selected={!!field.input.value}
            />;
        },
        onAccountSelected: function(input, accountId) {
            input.onChange(input.value ? null : accountId);
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
            return (<span>
                {accountSelects}
            </span>);
        }
    });

    return AccountSelectComponent;
});