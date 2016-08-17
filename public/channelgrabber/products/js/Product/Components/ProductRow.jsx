define([
    'react',
    'Product/Components/Checkbox',
    'Product/Components/Image',
    'Product/Components/Header',
    'Product/Components/Details',
    'Product/Components/Status',
    'Product/Components/VariationView',
    'Product/Components/DetailView'
], function(
    React,
    Checkbox,
    ProductImage,
    Header,
    Details,
    Status,
    VariationView,
    DetailView
) {
    "use strict";

    var ProductRowComponent = React.createClass({
        getProductVariationsView: function()
        {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return <VariationView attributeNames={this.props.product.attributeNames} variations={this.props.variations}/>;
            }
        },
        getProductDetailView: function()
        {
            return <DetailView variations={this.props.variations}/>;
        },
        getProductFooter: function()
        {
            if (this.props.product.variationCount > 2) {
                return <span>Expand Variations</span>
            }
        },
        render: function()
        {
            return (
                <div className="product-container " id={"product-container-" + this.props.product.id}>
                    <input type="hidden" value={this.props.product.id} name="id" />
                    <input type="hidden" value={this.props.product.eTag} name={"product[" + this.props.product.id + "][eTag]"} />
                    <Checkbox id={this.props.product.id} />
                    <div className="product-holder">
                        <div className="product-image-container">
                            <img src={this.props.product.images.length > 0 ? this.props.product.images[0]['url'] : this.context.imageBasePath + '/noproductsimage.png'} />
                        </div>
                    </div>
                    <div className="product-info-container">
                        <div className="product-header">
                            <b>{this.props.product.name}</b>
                            <span className="product-sku">{this.props.product.sku}</span>
                            <Status listings={this.props.product.listings} />
                        </div>
                        <div className="product-content-container">
                            <div className="variations-layout-column">
                                {this.getProductVariationsView()}
                            </div>
                            {this.getProductDetailView()}
                        </div>
                        <div className="product-footer">
                            {this.getProductFooter()}
                        </div>
                    </div>
                </div>
            );
        }
    });

    ProductRowComponent.contextTypes = {
        imageBasePath: React.PropTypes.string
    };

    return ProductRowComponent;
});