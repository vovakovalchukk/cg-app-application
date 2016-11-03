define([
    'react'
], function(
    React
) {
    "use strict";

    var StatusComponent = React.createClass({
        productStatusDecider: function() {
            var statusPrecedence = {
                'inactive': 1,
                'ended': 1,
                'active': 2,
                'pending': 3,
                'paused': 4,
                'error': 5
            };

            var status = 'inactive';
            for (var listing in this.props.listings) {
                var listingStatus = this.props.listings[listing]['status'];
                if(statusPrecedence[listingStatus] > statusPrecedence[status]) {
                    status = listingStatus;
                }
            }
            return status;
        },
        getStatusRows: function () {
            var self = this;
            return this.props.listings.map(function(listing) {
                var account = self.props.accounts[listing.accountId];
                if (account === undefined) {
                    return;
                }
                return (
                    <tr key={listing.id}>
                        <td>
                            <span className={"product-listing-status-row status " + listing.status}>
                                {listing.status}
                                {listing.message ? <span className={"tooltip status " + listing.status}>{listing.message}</span> : ''}
                            </span>
                        </td>
                        <td><a href={listing.url} target="_blank">{account ? account.displayName : ' '}</a></td>
                    </tr>
                );
            });
        },
        render: function() {
            var productStatus = this.productStatusDecider();
            return (
                <span className="product-status-holder">
                    <span className={"status " + productStatus}>{productStatus}</span>
                    <div className="product-listing-status-dropdown">
                        <table>
                            <tbody>
                            <tr>
                                <td>Status</td>
                                <td>Account</td>
                            </tr>
                            {this.getStatusRows()}
                            </tbody>
                        </table>
                    </div>
                </span>
            );
        }
    });

    return StatusComponent;
});