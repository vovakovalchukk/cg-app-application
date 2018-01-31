define([
    'react',
    'react-tether',
    'Product/Utils/CreateListingUtils',
    'Product/Components/Tooltip'
], function(
    React,
    TetherComponent,
    CreateListingUtils,
    Tooltip
) {
    "use strict";

    var CreateListingIconComponent = React.createClass({
        getDefaultProps: function() {
            return {
                accountsAvailable: {},
                isSimpleProduct: false,
                productId: null,
                availableChannels: {},
                onCreateListingIconClick: function() {}
            }
        },
        hasAccountsToListTo: function() {
            for (var accountId in this.props.accountsAvailable) {
                var account = this.props.accountsAvailable[accountId];
                if (CreateListingUtils.productCanListToAccount(account, this.props.availableChannels)) {
                    return true;
                }
            }
        },
        onClick: function() {
            this.props.onCreateListingIconClick(this.props.productId);
        },
        render: function() {
            if (this.props.isSimpleProduct && this.hasAccountsToListTo()) {
                return <i className="fa fa-plus icon-create-listing" onClick={this.onClick.bind(this)} aria-hidden="true" />
            }

            var availableChannelsString = 'no';
            if (!(Object.keys(this.props.availableChannels).length === 0)) {
                availableChannelsString = Object.values(this.props.availableChannels).join(', ');
            }

            var hoverContent = <div>
                <p>We only currently support creating listings on {availableChannelsString} accounts for simple products.</p>
                <p>We're working hard to add support for other channels so check back soon.</p>
            </div>;

            return <Tooltip hoverContent={hoverContent}>
                <i className="fa fa-plus icon-create-listing inactive" aria-hidden="true" />
            </Tooltip>;
        }
    });

    return CreateListingIconComponent;
});