define([
    'react',
    'react-tether'
], function(
    React,
    TetherComponent
) {
    "use strict";

    var LinkComponent = React.createClass({
        getDefaultProps: function () {
            return {
                sku: "",
                productLinks: []
            }
        },
        getInitialState: function() {
            return {
                hover: false,
                fetchingLinks: true,
            }
        },
        componentDidMount: function()
        {
            window.addEventListener('fetchingProductLinksStart', this.onStartFetchingLinks, false);
            window.addEventListener('fetchingProductLinksStop', this.onStopFetchingLinks, false);
        },
        componentWillUnmount: function()
        {
            window.removeEventListener('fetchingProductLinksStart', this.onStartFetchingLinks, false);
            window.removeEventListener('fetchingProductLinksStop', this.onStopFetchingLinks, false);
        },
        onStartFetchingLinks: function () {
            this.setState({ fetchingLinks: true });
        },
        onStopFetchingLinks: function () {
            this.setState({ fetchingLinks: false });
        },
        onMouseOver: function () {
            this.setState({ hover: true });
        },
        onMouseOut: function () {
            this.setState({ hover: false });
        },
        onClick: function () {
            window.triggerEvent('productLinkEditClicked', {sku: this.props.sku, productLinks: this.props.productLinks});
        },
        onLinkRowClick: function (sku) {
            window.triggerEvent('productLinkSkuClicked', {sku: sku});
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
                         className="hover-link-row"
                         onClick={this.onLinkRowClick.bind(this, linkedProduct.sku)}
                         title="Click to search for this product."
                    >
                        <span className="thumbnail"><img src={linkedProduct.imageUrl}/></span>
                        <span className="sku">{linkedProduct.sku}</span>
                        <span className="stock">{linkedProduct.quantity}</span>
                    </div>
                );
            }.bind(this));
        },
        getLinkIcon: function () {
            if (this.state.fetchingLinks) {
                return (
                    <img
                        title="Loading Product Links..."
                        src="/channelgrabber/zf2-v4-ui/img/loading.gif"
                        className="b-loader"
                    />
                );
            }
            var spriteClass = (this.props.productLinks.length ? 'sprite-linked-22-blue' : 'sprite-linked-22-white');
            return (
                <span className={"sprite "+ spriteClass}
                      title="Click to edit the linked products."
                      onClick={this.onClick}
                      onMouseOver={this.onMouseOver}
                      onMouseOut={this.onMouseOut}
                ></span>
            );
        },
        render: function() {
            var hoverImageStyle = {
                display: (this.state.hover ? "block" : "none")
            };
            return (
                <TetherComponent
                    attachment="top left"
                    targetAttachment="middle right"
                    constraints={[{
                        to: 'scrollParent',
                        attachment: 'together'
                    }]}
                >
                    {this.getLinkIcon()}
                    <div className="hover-link"
                         style={hoverImageStyle}
                         onMouseOver={this.onMouseOver}
                         onMouseOut={this.onMouseOut}>
                        {this.getHoverContent()}
                    </div>
                </TetherComponent>
            );
        }
    });

    return LinkComponent;
});