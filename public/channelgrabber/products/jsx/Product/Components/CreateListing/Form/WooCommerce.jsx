import React from 'react';
import Select from 'Common/Components/Select';
import CurrencyInput from 'Common/Components/CurrencyInput';
import Input from 'Common/Components/Input';
import CategorySelect from 'Product/Components/CreateListing/Form/WooCommerce/CategorySelect';
import VariationPicker from 'Product/Components/CreateListing/Form/Shared/VariationPicker';
import SimpleProduct from 'Product/Components/CreateListing/Form/Shared/SimpleProduct';
import ImagePicker from 'Common/Components/ImagePicker';


class WooCommerce extends React.Component {
    static defaultProps = {
        title: null,
        description: null,
        accountId: null,
        product: null,
        variationsDataForProduct: [],
        price: null
    };

    state = {
        error: false,
        shippingService: null,
        rootCategories: null,
    };

    componentDidMount() {
        this.fetchAndSetChannelSpecificFieldValues();
    }

    componentWillReceiveProps(newProps) {
        if (this.props.accountId != newProps.accountId) {
            this.fetchAndSetChannelSpecificFieldValues(newProps.accountId);
        }
    }

    fetchAndSetChannelSpecificFieldValues = (newAccountId) => {
        var accountId = newAccountId ? newAccountId : this.props.accountId;

        $.ajax({
            context: this,
            url: '/products/create-listings/' + accountId + '/channel-specific-field-values',
            type: 'GET',
            success: function (response) {
                this.setState({
                    rootCategories: response.categories
                });
            }
        });
    };

    refreshCategories = () => {
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
    };

    onInputChange = (event) => {
        var newStateObject = {};
        newStateObject[event.target.name] = event.target.value;
        this.props.setFormStateListing(newStateObject);
    };

    onLeafCategorySelected = (categoryId) => {
        this.props.setFormStateListing({category: categoryId});
    };

    getTooltipText = (inputFieldName) => {
        var tooltips = {
            title: 'An effective title should include brand name and item specifics. Reiterate what your item actually is to make it easy to find',
            description: 'Describe your item in detail. Be sure to include all item specifics like size shape and colour. Clearly state the item\'s condition such as new or used',
            category: 'Select a category to list your product to',
        };
        return tooltips[inputFieldName];
    };

    onImageSelected = (image) => {
        this.props.setFormStateListing({
            imageId: image.id
        });
    };

    renderVariationSpecificFields = () => {
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
    };

    renderImagePicker = () => {
        if (this.props.product.images.length == 0) {
            return (
                <p>No images available</p>
            );
        }
        return (
            <ImagePicker
                name="image"
                multiSelect={false}
                images={this.props.product.images}
                onImageSelected={this.onImageSelected}
                title={this.getTooltipText('image')}
            />
        );
    };

    render() {
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
            <label>
                <span className={"inputbox-label"}>Primary image</span>
                {this.renderImagePicker()}
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
}

export default WooCommerce;
