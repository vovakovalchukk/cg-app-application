import React from 'react';
"use strict";

class ListingsRowComponent extends React.Component {
    static defaultProps = {
        accounts: {},
        listings: {}
    };

    getHoverText = (listing) => {
        var hoverText = {
            'active': 'This is an active listing with available stock',
            'pending': 'We have recently sent a stock update to this listing, and are currently waiting for ' + $.trim(listing.channel) + ' to confirm they have received and processed the stock update',
            'paused': 'Listing is paused due to no stock being available for sale',
            'error': 'We received an error when sending a stock update for this listing and so we are not currently able to manage the stock for this listing.',
            'inactive': 'You do not currently have this SKU listed in this location',
            'unimported': 'This listing has not yet been imported or does not exist'
        };
        return hoverText[$.trim(listing.status)];
    };

    getValues = () => {
        var values = [];
        for (var accountId in this.props.listingsPerAccount) {
            this.props.listingsPerAccount[accountId].map(function(listingId) {
                if (this.props.listings.hasOwnProperty(listingId)) {
                    var listing = this.props.listings[listingId];
                    var status = $.trim(listing.status);
                    var listingUrl = $.trim(listing.url);
                    values.push(<td title={this.getHoverText(listing)}><a target="_blank" href={listingUrl}><span
                        className={"listing-status " + status}></span></a></td>);
                } else {
                    values.push(<td title={this.getHoverText({status: 'unimported'})}><span
                        className={"listing-status unknown"}></span></td>);
                }
            }.bind(this));
        }
        return values;
    };

    render() {
        return <tr>{this.getValues()}</tr>;
    }
}

export default ListingsRowComponent;
