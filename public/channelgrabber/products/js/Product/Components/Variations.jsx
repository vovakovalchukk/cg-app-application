define([
    'react'
], function(
    React
) {
    "use strict";

    var VariationsComponent = React.createClass({
        render: function()
        {
            console.log(this.props.data.listings);
            var listings = this.props.data.listings.map(function(object) {


            });
            return (
                <div className="product-container " id={"product-container-" + this.props.data.id}>
                    <input type="hidden" value={this.props.data.id} name="id" />
                    <input type="hidden" value={this.props.data.eTag} name={"product[" + this.props.data.id + "][eTag]"} />
                    <Checkbox id={this.props.data.id} />
                    <ProductImage url='http://placekitten.com/500/500' />
                    <Variations products={this.props.data.listings} />
                </div>
            );
        }
    });

    return VariationsComponent;
});