define([
    'react',
    'Common/Components/Select',
    'Product/Utils/CreateListingUtils'
], function(
    React,
    Select,
    CreateListingUtils
) {
    "use strict";

    var AccountPickerComponent = React.createClass({
        getDefaultProps: function() {
            return {
                accounts: {},
                accountsProductIsListedOn: []
            }
        },
        getSelectOptions: function() {
            var options = [{name: null, value: null}];
            for (var accountId in this.props.accounts) {
                var account = this.props.accounts[accountId];
                if (CreateListingUtils.productCanListToAccount(account, this.props.accountsProductIsListedOn)) {
                    options.push({name: account.displayName, value: account.id});
                }
            }

            return options;
        },
        render: function() {
            return <Select options={this.getSelectOptions()} onOptionChange={this.props.onAccountSelected}/>
        }
    });

    return AccountPickerComponent;
});