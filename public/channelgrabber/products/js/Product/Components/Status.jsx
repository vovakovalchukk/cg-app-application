define([
    'react'
], function(
    React
) {
    "use strict";

    var StatusComponent = React.createClass({
        productStatusDecider: function()
        {
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
        render: function()
        {
            var rows = [];

            this.props.listings.forEach(function(listing) {
                rows.push(
                    <tr key={listing.id}>
                        <td><span className={"status " + listing.status} title="">{listing.status}</span></td>
                        <td><a href={listing.url} target="_blank">{listing.channel}</a></td>
                    </tr>
                );
            });
            return (
                <span className="product-status-holder">
                    <span className={"status " + this.productStatusDecider()}>{this.productStatusDecider()}</span>
                    <div className="product-listing-status-dropdown true">
                        <table>
                            <tbody>
                            <tr>
                                <td>Status</td>
                                <td>Account</td>
                            </tr>
                            {rows}
                            </tbody>
                        </table>
                    </div>
                </span>
            );
        }
    });

    return StatusComponent;
});