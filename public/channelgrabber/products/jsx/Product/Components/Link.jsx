define([
    'react',
    'react-tether'
], function(
    React,
    TetherComponent
) {
    "use strict";

    var LinkComponent = React.createClass({
        getInitialState: function() {
            return {
                sku: "",
                hover: false,
                retrievedProducts: false,
                linkedProducts: []
            }
        },
        onMouseOver: function () {
            if (this.state.retrievedProducts) {
                this.setState({hover: true});
                return;
            }

            this.fetchLinkedProducts();
            this.setState({hover: true});
        },
        onMouseOut: function () {
            this.setState({ hover: false });
        },
        fetchLinkedProducts: function () {
            $.ajax({
                url: '/products/links/ajax',
                type: 'POST',
                data: { sku: this.props.sku },
                success: function (response) {
                    var products = [];
                    if (response.linkedProducts) {
                        products = response.linkedProducts;
                    }
                    this.setState({
                        linkedProducts: products,
                        retrievedProducts: true
                    });
                },
                error: function(error) {
                    console.warn(error);
                }
            });
        },
        getHoverContent: function () {
            if (this.state.linkedProducts.length === 0 && this.state.retrievedProducts === false) {
                return (
                    <img src="/channelgrabber/zf2-v4-ui/img/loading.gif" className="b-loader" />
                );
            }

            return this.state.linkedProducts.map(function (linkedProduct) {
                return (
                    <div className="hover-link-row">
                        <span></span>
                    </div>
                );
            })
        },
        render: function() {
            var hoverImageStyle = {
                display: (this.state.hover && this.state.linkedProducts.length === 0 && this.state.retrievedProducts ? "block" : "none")
            };
            var spriteClass = (this.state.linkedProducts.length ? 'sprite-linked-22-blue' : 'sprite-linked-22-white');
            return (
                <TetherComponent
                    attachment="middle left"
                    targetAttachment="middle right"
                    constraints={[{
                        to: 'scrollParent',
                        attachment: 'together'
                    }]}
                >
                    <span className={"sprite "+ spriteClass}
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