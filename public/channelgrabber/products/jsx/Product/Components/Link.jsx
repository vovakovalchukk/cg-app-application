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
                linkedProducts: []
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
        onClick: function () {
            window.triggerEvent('productLinkEditClicked', {sku: this.props.sku, linkedProducts: this.props.linkedProducts});
        },
        onLinkRowClick: function (sku) {
            window.triggerEvent('productLinkSkuClicked', {sku: sku});
        },
        getHoverContent: function () {
            if (this.props.linkedProducts.length === 0) {
                return (
                    <div className="hover-link-none-msg">
                        <span>No linked products</span>
                    </div>
                );
            }

            return this.props.linkedProducts.map(function(linkedProduct) {
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
        render: function() {
            var hoverImageStyle = {
                display: (this.state.hover ? "block" : "none")
            };
            var spriteClass = (this.props.linkedProducts.length ? 'sprite-linked-22-blue' : 'sprite-linked-22-white');
            return (
                <TetherComponent
                    attachment="top left"
                    targetAttachment="middle right"
                    constraints={[{
                        to: 'scrollParent',
                        attachment: 'together'
                    }]}
                >
                    <span className={"sprite "+ spriteClass}
                          title="Click to edit the linked products."
                          onClick={this.onClick}
                          onMouseOver={this.onMouseOver}
                          onMouseOut={this.onMouseOut}
                    ></span>
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