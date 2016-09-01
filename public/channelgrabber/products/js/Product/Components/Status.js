define(['react'], function (React) {
    "use strict";

    var StatusComponent = React.createClass({
        displayName: 'StatusComponent',

        productStatusDecider: function () {
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
                if (statusPrecedence[listingStatus] > statusPrecedence[status]) {
                    status = listingStatus;
                }
            }
            return status;
        },
        getStatusRows: function () {
            return this.props.listings.map(function (listing) {
                return React.createElement(
                    'tr',
                    { key: listing.id },
                    React.createElement(
                        'td',
                        null,
                        React.createElement(
                            'span',
                            { className: "product-listing-status-row status " + listing.status },
                            listing.status,
                            listing.message ? React.createElement(
                                'span',
                                { className: "tooltip status " + listing.status },
                                listing.message
                            ) : ''
                        )
                    ),
                    React.createElement(
                        'td',
                        null,
                        React.createElement(
                            'a',
                            { href: listing.url, target: '_blank' },
                            listing.channel
                        )
                    )
                );
            });
        },
        render: function () {
            var productStatus = this.productStatusDecider();
            return React.createElement(
                'span',
                { className: 'product-status-holder' },
                React.createElement(
                    'span',
                    { className: "status " + productStatus },
                    productStatus
                ),
                React.createElement(
                    'div',
                    { className: 'product-listing-status-dropdown' },
                    React.createElement(
                        'table',
                        null,
                        React.createElement(
                            'tbody',
                            null,
                            React.createElement(
                                'tr',
                                null,
                                React.createElement(
                                    'td',
                                    null,
                                    'Status'
                                ),
                                React.createElement(
                                    'td',
                                    null,
                                    'Account'
                                )
                            ),
                            this.getStatusRows()
                        )
                    )
                )
            );
        }
    });

    return StatusComponent;
});
