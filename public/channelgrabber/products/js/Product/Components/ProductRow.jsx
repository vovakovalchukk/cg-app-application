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
            if (this.props.data.variationCount !== undefined && this.props.data.variationCount > 1) {
                return <VariationView variations={this.props.data.variations}/>;

            }
        },
        getProductDetailView: function()
        {
            return <DetailView/>;
        },
        getProductFooter: function()
        {
            if (this.props.data.variationCount > 2) {
                return <span>Expand Variations</span>
            }
        },
        render: function()
        {
            return (
                <div className="product-container " id={"product-container-" + this.props.data.id}>
                    <input type="hidden" value={this.props.data.id} name="id" />
                    <input type="hidden" value={this.props.data.eTag} name={"product[" + this.props.data.id + "][eTag]"} />
                    <Checkbox id={this.props.data.id} />
                    <ProductImage images={this.props.data.images} imageBasePath={this.props.imageBasePath} />
                    <div className="product-info-container">
                        <div className="product-header">
                            <b>{this.props.data.name}</b>
                            <span className="product-sku">{this.props.data.sku}</span>
                            <Status listings={this.props.data.listings} />
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

    return ProductRowComponent;
});