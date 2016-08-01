define([
    'react',
    'Product/Components/Header',
    'Product/Components/Details'
], function(
    React,
    Header,
    Details
) {
    "use strict";

    var InfoComponent = React.createClass({
        render: function()
        {
            return (
                <div className="product-info-container">
                    <Header name={this.props.data.name} sku={this.props.data.sku} listings={this.props.data.listings} />
                    <Details listings={this.props.data.listings} />
                </div>
            );
        }
    });

    return InfoComponent;
});