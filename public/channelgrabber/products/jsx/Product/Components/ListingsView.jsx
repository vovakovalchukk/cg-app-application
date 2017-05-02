define([
    'react',
    'Product/Components/ListingsRow'
], function(
    React,
    ListingsRow
) {
    "use strict";

    const listingColumnWidth = 100;// pixels

    var ListingsViewComponent = React.createClass({
        getHeaders: function() {
            var headers = [];
            for (var accountId in this.props.maxListingsPerAccount) {
                if (!this.props.maxListingsPerAccount.hasOwnProperty(accountId)) continue;

                var maxListings = this.props.maxListingsPerAccount[accountId];
                var account = this.props.variations[0].accounts[accountId];
                for (var i = 0; i < maxListings; i++) {
                    headers.push(<th title={account.displayName} style={{width: listingColumnWidth}}>{account.channel}</th>);
                }
            }
            return headers;
        },
        getDefaultProps: function() {
            return {
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
                            return <ListingsRow key={variation.id} listings={variation.listingsPerAccount} maxListingsPerAccount={this.props.maxListingsPerAccount}/>;
                        }.bind(this))}
                        </tbody>
                    </table>
                </div>
            );
        }
    });

    return ListingsViewComponent;
});