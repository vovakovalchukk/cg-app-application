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
        getHoverText: function (status) {
            var hoverText = {
                'active': 'This is an active listing with available stock',
                'pending': 'We have recently sent a stock update to this listing, and are currently waiting for X (X= ebay, amazon, whatever it is) to confirm they have received and processed that stock update',
                'paused': 'Listing is paused due to no stock being available for sale',
                'error': 'We received an error when sending a stock update for this listing and so we are not currently able to manage the stock for this listing. Click here for the full error message, please contact support if you need assistance.',
                'inactive': 'You do not currently have this product listed in this location',
                'unimported': 'This listing has not yet been imported or is not available'
            };
            return hoverText[status];
        },
        getValues: function() {
            var values = [];
            for (var accountId in this.props.listingsPerAccount) {
                this.props.listingsPerAccount[accountId].map(function(listingId) {
                    if (this.props.listings.hasOwnProperty(listingId)) {
                        var listing = this.props.listings[listingId];
                        var status = $.trim(listing.status);
                        var listingUrl = $.trim(listing.url);
                        values.push(<td title={this.getHoverText(status)}><a target="_blank" href={listingUrl}><span className={"listing-status " + status}></span></a></td>);
                    } else {
                        values.push(<td title={this.getHoverText('unimported')}><span className={"listing-status unknown"}></span></td>);
                    }
                }.bind(this));
            }
            return values;
        },
        render: function () {
            return <tr>{this.getValues()}</tr>;
        }
    });

    return ListingsRowComponent;
});
