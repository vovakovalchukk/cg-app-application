define([
    'react',
    'Product/Components/Status'
], function(
    React,
    Status
) {
    "use strict";

    var HeaderComponent = React.createClass({
        render: function()
        {
            return (
                <div className="product-header">
                    <b>{this.props.name}</b>
                    <span className="product-sku">{this.props.sku}</span>
                    <Status listings={this.props.listings} />
                </div>
            );
        }
    });

    return HeaderComponent;
});