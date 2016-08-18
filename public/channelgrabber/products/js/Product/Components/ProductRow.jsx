define([
    'react',
    'Product/Components/Checkbox',
    'Product/Components/Status',
    'Product/Components/VariationView',
    'Product/Components/DetailView',
    'Product/Components/Button',
    'Product/Filter/Entity',
    'Product/Storage/Ajax'
], function(
    React,
    Checkbox,
    Status,
    VariationView,
    DetailView,
    Button,
    ProductFilter,
    AjaxHandler
) {
    "use strict";

    var ProductRowComponent = React.createClass({
        getProductVariationsView: function()
        {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return <VariationView attributeNames={this.props.product.attributeNames} variations={this.state.variations}/>;
            }
        },
        getDetailsView: function ()
        {
            var products = [this.props.product];
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                products = this.state.variations;
            }
            return  <DetailView variations={products}/>
        },
        getExpandVariationsButton: function()
        {
            if (this.props.product.variationCount !== undefined && this.props.product.variationCount > 1) {
                return <Button text={(this.state.expanded ? 'Contract' : 'Expand') + " Variations"} onClick={this.expandButtonClicked}/>
            }
        },
        expandButtonClicked: function (e) {
            e.preventDefault();

            if (this.state.variations.length <= 2)  {
                $('#products-loading-message').show();
                var filter = new ProductFilter(null, this.props.product.id);
                AjaxHandler.fetchByFilter(filter, function(data) {
                    this.setState({variations: data.products});
                    $('#products-loading-message').hide();
                }.bind(this));

            }
            this.setState({
                expanded: !this.state.expanded
            })
        },
        getInitialState: function () {
            return {
                expanded: false,
                variations: []
            };
        },
        componentWillReceiveProps: function (newProps) {
            this.setState({
                variations: newProps.variations
            })
        },
        render: function()
        {
            return (
                <div className="product-container" id={"product-container-" + this.props.product.id}>
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
                        <div className={"product-content-container" + (this.state.expanded ? "" : " contracted")}>
                            <div className="variations-layout-column">
                                {this.getProductVariationsView()}
                            </div>
                            {this.getDetailsView()}
                        </div>
                        <div className="product-footer">
                            <div className="variations-button-holder">
                                {this.getExpandVariationsButton()}
                            </div>
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