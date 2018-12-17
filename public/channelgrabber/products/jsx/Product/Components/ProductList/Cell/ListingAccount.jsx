import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import ListingStatus from 'Product/Components/ListingStatus';

"use strict";

const LISTING_STATUSES_BY_PRIORITY = [
    {
        status: 'active',
        getHoverMessage: () => ('This is an active listing with available stock'),
        statusPriority: 0
    }, {
        status: 'pending',
        getHoverMessage: (listing) => ('We have recently sent a stock update to this listing, and are currently waiting for ' + $.trim(listing.channel) + ' to confirm they have received and processed the stock update'),
        statusPriority: 1
    }, {
        status: 'paused',
        getHoverMessage: () => ('Listing is paused due to no stock being available for sale'),
        statusPriority: 2
    }, {
        status: 'error',
        getHoverMessage: () => ('We received an error when sending a stock update for this listing and so we are not currently able to manage the stock for this listing.'),
        statusPriority: 3
    }, {
        status: 'inactive',
        getHoverMessage: () => ('You do not currently have this SKU listed in this location'),
        statusPriority: 4
    }, {
        status: 'uninmported',
        getHoverMessage: () => ('This listing has not yet been imported or does not exist'),
        statusPriority: 5
    }
];

class ListingAccountCell extends React.Component {
    static defaultProps = {
        actions: {},
        rowIndex: null,
        products: {},
        listingAccountId: null
    };

    state = {};

    onAddListingClick = async () => {
        const {products, rowIndex} = this.props;
        const rowData = stateUtility.getRowData(products, rowIndex);
        this.props.actions.createNewListing({
            rowData
        });
    };

    render() {
        let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
        let listingsForAccount = getListingsForAccount(row, this.props.listingAccountId);
        let mostNegativeListing = getMostNegativeListing(listingsForAccount);
        let mostNegativeListingStateFromListings = getMostNegativeListingStatusFromListings(mostNegativeListing);

        let listingUrl = getListingUrl(mostNegativeListing);
        let listingTitle = getListingTitle(mostNegativeListing);

        let {status} = mostNegativeListingStateFromListings;

        return <ListingStatus
            status={status}
            title={listingTitle}
            onAddListingClick={this.onAddListingClick}
            className={this.props.className}
            listingUrl={listingUrl}
        />;
    }
}

export default ListingAccountCell;

function getListingsForAccount(rowData, listingAccountId) {
    let {listingsPerAccount, listings} = rowData;
    if (!listingsPerAccount) {
        return;
    }
    let listingsIdsForAccount = listingsPerAccount[listingAccountId];
    if (!listingsIdsForAccount) {
        return;
    }

    return listingsIdsForAccount.map((listingId) => {
        return listings[listingId];
    });
}

function getListingTitle(listing) {
    if (!listing) {
        return '';
    }
    let relevantListingStatus = LISTING_STATUSES_BY_PRIORITY.find(status => {
        return listing.status === status.status;
    });
    if(relevantListingStatus.status === 'error' && listing.message){
        return listing.message;
    }
    return relevantListingStatus.getHoverMessage(listing);
}

function getListingUrl(listing) {
    if (!listing) {
        return '';
    }
    return listing.status === 'active' ? listing.url : '';
}

function getMostNegativeListing(listings) {
    if (!listings) {
        return null;
    }
    let mostNegativeListing = listings[0];
    listings.forEach((listing) => {
        let relevantListingStatus = LISTING_STATUSES_BY_PRIORITY.find(status => {
            return listing.status === status.status;
        });
        if (relevantListingStatus.statusPriority > mostNegativeListing.status.statusPriority) {
            mostNegativeListing = relevantListingStatus;
        }
    });
    return mostNegativeListing;
}

function getMostNegativeListingStatusFromListings(mostNegativeListing) {
    if (!mostNegativeListing) {
        return LISTING_STATUSES_BY_PRIORITY.find(status => (status.status === 'inactive'));
    }
    return LISTING_STATUSES_BY_PRIORITY.find(LISTING_STATUS => {
        return LISTING_STATUS.status === mostNegativeListing.status;
    });
}