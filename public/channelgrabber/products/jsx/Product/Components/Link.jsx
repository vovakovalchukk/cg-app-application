define([
    'react',
    'Product/Components/Tooltip',
    'Product/Components/ProductList/Config/constants'
], function(
    React,
    Tooltip,
    constants
) {
    "use strict";

    const {LINK_STATUSES} = constants;
    
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
        componentDidMount: function()
        {
            if(this.props.linkStatus = LINK_STATUSES.fetching ){
                // this.onStartFetchingLinks();/
            }
            // window.addEventListener('fetchingProductLinksStart', this.onStartFetchingLinks, false);
            // window.addEventListener('fetchingProductLinksStop', this.onStopFetchingLinks, false);
        },
        // componentWillUnmount: function()
        // {
        //     window.removeEventListener('fetchingProductLinksStart', this.onStartFetchingLinks, false);
        //     window.removeEventListener('fetchingProductLinksStop', this.onStopFetchingLinks, false);
        // },
        // onStartFetchingLinks: function () {
        //     this.setState({ fetchingLinks: true });
        // },
        // onStopFetchingLinks: function () {
        //     this.setState({ fetchingLinks: false });
        // },
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
            
            // console.log('in getLinkIcon this.props.linkStatus: ' , this.props.linkStatus);
            
            
            
            if (this.props.linkStatus == LINK_STATUSES.fetching) {
                return (
                    <span>
                        <img
                            title="Loading Product Links..."
                            src="/channelgrabber/zf2-v4-ui/img/loading-transparent-21x21.gif"
                            className="b-loader"
                        />
                    </span>
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