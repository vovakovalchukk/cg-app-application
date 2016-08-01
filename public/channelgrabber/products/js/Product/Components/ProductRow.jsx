define([
    'react',
    'Product/Components/Checkbox',
    'Product/Components/Image',
    'Product/Components/Information'
], function(
    React,
    Checkbox,
    ProductImage,
    ProductInfo
) {
    "use strict";

    var ProductRowComponent = React.createClass({
        render: function()
        {
            return (
                <div className="product-container " id={"product-container-" + this.props.data.id}>
                    <input type="hidden" value={this.props.data.id} name="id" />
                    <input type="hidden" value={this.props.data.eTag} name={"product[" + this.props.data.id + "][eTag]"} />
                    <Checkbox id={this.props.data.id} />
                    <ProductImage url='http://placekitten.com/500/500' />
                    <ProductInfo data={this.props.data} />
                </div>
            );
        }
    });

    return ProductRowComponent;
});