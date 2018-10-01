import Skeleton from 'react-skeleton-loader';

define([
    'react',
    'Product/Components/Tooltip',
    'Product/Components/ProductList/Config/constants',
], function(
    React,
    Tooltip,
    constants
) {
    "use strict";

    const {LINK_STATUSES} = constants;
    const LINK_ICON_DIMENSIONS = {
        width: 22,
        height: 22
    };
    
    var LinkComponent = React.createClass({
        getDefaultProps: function () {
            return {
                sku: "",
                productLinks: [],
                linkStatus: ''
            }
        },
        getInitialState: function() {
            return {
                fetchingLinks: false,
            }
        },
        onClick: function () {
            window.triggerEvent('productLinkEditClicked', {sku: this.props.sku, productLinks: this.props.productLinks});
        },
        onLinkRowClick: function (sku) {
            window.triggerEvent('getProductsBySku', {sku: [sku]});
        },
        getHoverContent: function () {
            if (this.props.productLinks.length === 0) {
                return (
                    <div className="hover-link-none-msg">
                        <span>No linked products</span>
                    </div>
                );
            }

            return this.props.productLinks.map(function(linkedProduct) {
                return (
                    <div key={linkedProduct.sku}
                         className="product-link hover-link-row"
                         onClick={this.onLinkRowClick.bind(this, linkedProduct.sku)}
                         title="Click to search for this product."
                    >
                        <span className="thumbnail">
                            <img src={linkedProduct.product ? this.context.imageUtils.getProductImage(linkedProduct.product, linkedProduct.sku) : ''} />
                        </span>
                        <span className="sku">{linkedProduct.sku}</span>
                        <span className="stock">{linkedProduct.quantity}</span>
                    </div>
                );
            }.bind(this));
        },
        getLinkIcon: function () {
            if (this.props.linkStatus == LINK_STATUSES.fetching) {
                return (
                    <Skeleton
                        width={LINK_ICON_DIMENSIONS.height + 'px'}
                        height={LINK_ICON_DIMENSIONS.height + 'px'}
                        borderRadius={(LINK_ICON_DIMENSIONS.height / 2) + 'px'}
                    />
                );
            }
            var spriteClass = (this.props.productLinks.length ? 'sprite-linked-22-blue' : 'sprite-linked-22-white');
            return (
                <span className={"sprite "+ spriteClass + " click"}
                      onClick={this.onClick}
                ></span>
            );
        },
        render: function() {
            return <Tooltip hoverContent={this.getHoverContent()}>
                {this.getLinkIcon()}
            </Tooltip>;
        }
    });

    LinkComponent.contextTypes = {
        imageUtils: React.PropTypes.object
    };

    return LinkComponent;
});