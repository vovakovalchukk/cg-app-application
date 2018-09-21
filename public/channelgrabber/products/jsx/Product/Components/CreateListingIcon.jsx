import React from 'react';
import TetherComponent from 'react-tether';
import CreateListingUtils from 'Product/Utils/CreateListingUtils';
import Tooltip from 'Product/Components/Tooltip';
    

    var CreateListingIconComponent = React.createClass({
        getDefaultProps: function() {
            return {
                accountsAvailable: {},
                isSimpleProduct: false,
                productId: null,
                availableChannels: {},
                availableVariationsChannels: {},
                onCreateListingIconClick: function() {}
            }
        },
        hasAccountsToListTo: function(isSimpleProduct) {
            var accountsAvailableForProductType = isSimpleProduct ? this.props.availableChannels : this.props.availableVariationsChannels;

            for (var accountId in this.props.accountsAvailable) {
                var account = this.props.accountsAvailable[accountId];
                if (CreateListingUtils.productCanListToAccount(account, accountsAvailableForProductType)) {
                    return true;
                }
            }
        },
        onClick: function() {
            this.props.onCreateListingIconClick(this.props.productId);
        },
        render: function() {
            if (this.hasAccountsToListTo(this.props.isSimpleProduct)) {
                return <div className="icon-create-listing" onClick={this.onClick.bind(this)}>
                    Add Listing
                    <i className="fa fa-plus" aria-hidden="true" />
                </div>
            }

            var availableChannelsString = 'no';
            var availableVariationsChannelsString = 'no';
            var reasonString = '';
            if (!(Object.keys(this.props.availableChannels).length === 0)) {
                availableChannelsString = Object.values(this.props.availableChannels).join(', ');
            }
            if (!(Object.keys(this.props.availableVariationsChannels).length === 0)) {
                availableVariationsChannelsString = Object.values(this.props.availableVariationsChannels).join(', ');
            }

            if (this.props.isSimpleProduct) {
                reasonString = <p>We only currently support creating listings on {availableChannelsString} accounts for <b>simple</b> products.</p>;
            } else {
                reasonString = <p>We only currently support creating listings on {availableVariationsChannelsString} accounts for <b>variation</b> products.</p>;
            }

            var hoverContent = <div>
                {reasonString}
                <p>We're working hard to add support for other channels so check back soon.</p>
            </div>;

            return <div className="inactive icon-create-listing">
                <Tooltip hoverContent={hoverContent}>
                    Add Listing
                    <i className="fa fa-plus" aria-hidden="true" />
                </Tooltip>
            </div>;
        }
    });

    export default CreateListingIconComponent;
