define([
    'react',
    'react-tether',
    'Product/Utils/CreateListingUtils'
], function(
    React,
    TetherComponent,
    CreateListingUtils
) {
    "use strict";

    var CreateListingIconComponent = React.createClass({
        getDefaultProps: function() {
            return {
                accountsListedOn: [],
                accountsAvailable: {},
                isSimpleProduct: false,
                productId: null,
                availableChannels: {},
                onCreateListingIconClick: function() {}
            }
        },
        getInitialState: function() {
            return {
                hover: false
            }
        },
        onMouseOver: function () {
            this.setState({ hover: true });
        },
        onMouseOut: function () {
            this.setState({ hover: false });
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

            var hoverImageStyle = {
                display: (this.state.hover ? "block" : "none")
            };

            var availableChannelsString = 'no';
            if (!(Object.keys(this.props.availableChannels).length === 0)) {
                availableChannelsString = Object.values(this.props.availableChannels).join(', ');
            }

            return  <TetherComponent
                attachment="top left"
                targetAttachment="middle right"
                constraints={[{
                    to: 'scrollParent',
                    attachment: 'together'
                }]}
            >
                <i
                    className="fa fa-plus icon-create-listing inactive"
                    onMouseOver={this.onMouseOver.bind(this)}
                    onMouseOut={this.onMouseOut.bind(this)}
                    aria-hidden="true"
                />
                <div
                    className="hover-link"
                     style={hoverImageStyle}
                >
                    <p>We only currently support creating listings on {availableChannelsString} accounts for simple products.</p>
                    <p>We're working hard to add support for other channels so check back soon.</p>
                </div>
            </TetherComponent>;
        }
    });

    return CreateListingIconComponent;
});