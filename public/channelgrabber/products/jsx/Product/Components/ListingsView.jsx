define([
    'react',
    'Product/Components/ListingsRow'
], function(
    React,
    ListingsRow
) {
    "use strict";

    var ListingsViewComponent = React.createClass({
        getHeaders: function() {
            var headers = [];

            for (var accountId in this.props.accounts) {
                if (!this.props.accounts.hasOwnProperty(accountId)) continue;

                var channel = $.trim(this.props.accounts[accountId].channel);
                var displayName = $.trim(this.props.accounts[accountId].displayName);
                headers.push(<th title={displayName}>{channel}</th>);
            }

            return headers;
        },
        getDefaultProps: function() {
            return {
                accounts: [],
                variations: [],
                fullView: false
            };
        },
        render: function () {
            var count = 0;
            return (
                <div className="listings-table">
                    <table>
                        <thead>
                        <tr>
                            {this.getHeaders()}
                        </tr>
                        </thead>
                        <tbody>
                        {this.props.variations.map(function (variation) {
                            if ((! this.props.fullView) && count > 1) {
                                return;
                            }
                            count++;
                            return <ListingsRow key={variation.id} accounts={this.props.accounts} listings={variation.listingsPerAccount}/>;
                        }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return ListingsViewComponent;
});