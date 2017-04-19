define([
    'react'
], function(
    React
) {
    "use strict";

    var ListingsRowComponent = React.createClass({
        getDefaultProps: function() {
            return {
                accounts: {},
                listings: {}
            };
        },
        getValues: function() {
            var values = [];

            for (var accountId in this.props.accounts) {
                if (!this.props.accounts.hasOwnProperty(accountId)) continue;

                if (this.props.listings[accountId]) {
                    var status = $.trim(this.props.listings[accountId].status);
                    values.push(<td title={status}><span className={"status " + status}>{status}</span></td>);
                } else {
                    values.push(<td title="Unimported"><span className={"status not_started"}>Unimported</span></td>);
                }
            }

            return values;
        },
        render: function () {
            return <tr>{this.getValues()}</tr>;
        }
    });

    return ListingsRowComponent;
});