define([
    'react',
    'Common/Components/Select',
    'Common/Components/CurrencyInput',
    'Common/Components/Input',
    'Product/Components/CreateListing/Form/WooCommerce/CategorySelect',
    'Product/Components/CreateListing/Form/Shared/VariationPicker',
    'Product/Components/CreateListing/Form/Shared/SimpleProduct'
], function(
    React,
    Select,
    CurrencyInput,
    Input,
    CategorySelect,
    VariationPicker,
    SimpleProduct
) {
    "use strict";

    var WooCommerce = React.createClass({
        getDefaultProps: function() {
            return {
                title: null,
                description: null,
                accountId: null,
                product: null,
                variationsDataForProduct: [],
                price: null
            }
        },
        getInitialState: function() {
            return {
                error: false,
                shippingService: null,
                rootCategories: null,
            }
        },
        componentDidMount: function() {
            this.fetchAndSetChannelSpecificFieldValues();
        },
        componentWillReceiveProps(newProps) {
            if (this.props.accountId != newProps.accountId) {
                this.fetchAndSetChannelSpecificFieldValues(newProps.accountId);
            }
        },
        fetchAndSetChannelSpecificFieldValues: function(newAccountId) {
            var accountId = newAccountId ? newAccountId : this.props.accountId;

            $.ajax({
                context: this,
                url: '/products/create-listings/' + accountId + '/channel-specific-field-values',
                type: 'GET',
                success: function (response) {
                    this.setState({
                        rootCategories: response.category
                    });
                }
            });
        },
        refreshCategories() {
            this.setState({refreshCategoriesDisabled: true});
            $.get('/products/create-listings/' + this.props.accountId + '/refresh-categories', function(data) {
                if (data.error) {
                    n.error(data.error);
                }
                this.setState({
                    rootCategories: data.categories || [],
                    refreshCategoriesDisabled: false
                });
            }.bind(this));
        },
        onInputChange: function(event) {
            var newStateObject = {};
            newStateObject[event.target.name] = event.target.value;
            this.props.setFormStateListing(newStateObject);
        },
        onLeafCategorySelected(categoryId) {
            this.props.setFormStateListing({category: categoryId});
        },
        getTooltipText(inputFieldName) {
            var tooltips = {
                title: 'An effective title should include brand name and item specifics. Reiterate what your item actually is to make it easy to find',
                description: 'Describe your item in detail. Be sure to include all item specifics like size shape and colour. Clearly state the item\'s condition such as new or used',
                category: 'Select a category to list your product to',
            };
            return tooltips[inputFieldName];
        },
        renderVariationSpecificFields: function () {
            var variationsDataForProduct = this.props.variationsDataForProduct;
            var attributeNames = this.props.product.attributeNames;
            if (this.props.variationsDataForProduct.length == 0) {
                return <SimpleProduct
                    variationFormState={this.props.variations}
                    setFormStateListing={this.props.setFormStateListing}
                    customFields={{}}
                    currency={this.state.currency}
                    product={this.props.product}
                    price={this.props.price}
                />;
            }

            return <VariationPicker
                variationsDataForProduct={variationsDataForProduct}
                variationFormState={this.props.variations}
                setFormStateListing={this.props.setFormStateListing}
                attributeNames={attributeNames}
                editableAttributeNames={false}
                customFields={{}}
                currency={this.state.currency}
                fetchVariations={this.props.fetchVariations}
                product={this.props.product}
            />
        },
        render: function() {
            return <div>
                {this.renderVariationSpecificFields()}
                <label>
                    <span className={"inputbox-label"}>Listing Title:</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name='title'
                            value={this.props.title}
                            onChange={this.onInputChange}
                            title={this.getTooltipText('title')}
                        />
                    </div>
                </label>
                <label>
                    <span className={"inputbox-label"}>Description</span>
                    <div className={"order-inputbox-holder"}>
                        <Input
                            name="description"
                            value={this.props.description}
                            onChange={this.onInputChange}
                            title={this.getTooltipText('description')}
                        />
                    </div>
                </label>
                <CategorySelect
                    accountId={this.props.accountId}
                    rootCategories={this.state.rootCategories}
                    onLeafCategorySelected={this.onLeafCategorySelected}
                    refreshCategories={this.refreshCategories}
                    refreshCategoriesDisabled={this.state.refreshCategoriesDisabled}
                    title={this.getTooltipText('category')}
                />
            </div>;
        }
    });

    return WooCommerce;
});