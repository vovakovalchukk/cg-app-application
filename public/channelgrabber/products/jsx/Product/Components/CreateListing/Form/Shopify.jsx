import React from 'react';
import Select from 'Common/Components/Select';
import CurrencyInput from 'Common/Components/CurrencyInput';
import Input from 'Common/Components/Input';
import Button from 'Common/Components/Button';
import ImagePicker from 'Common/Components/ImagePicker';
import RefreshIcon from 'Common/Components/RefreshIcon';
import CategorySelect from 'Product/Components/CreateListing/Form/Shopify/CategorySelect';
import VariationPicker from 'Product/Components/CreateListing/Form/Shared/VariationPicker';
import SimpleProduct from 'Product/Components/CreateListing/Form/Shared/SimpleProduct';


class Shopify extends React.Component {
    static defaultProps = {
        title: null,
        description: null,
        price: null,
        accountId: null,
        brand: null,
        product: null,
        category: []
    };

    state = {
        error: false,
        categories: null,
        refreshCategoriesDisabled: false
    };

    componentDidMount() {
        this.fetchAndSetCategories();
    }

    componentWillReceiveProps(newProps) {
        if (this.props.accountId != newProps.accountId) {
            this.fetchAndSetCategories(newProps.accountId);
        }
    }

    fetchAndSetCategories = (newAccountId) => {
        var accountId = newAccountId ? newAccountId : this.props.accountId;

        $.get('/products/create-listings/' + accountId + '/channel-specific-field-values', function(data) {
            this.setState({categories: data.categories});
        }.bind(this));
    };

    refreshCategories = () => {
        this.setState({refreshCategoriesDisabled: true});
        $.get('/products/create-listings/' + this.props.accountId + '/refresh-categories', function(data) {
            if (data.error) {
                n.error(data.error);
            }
            this.setState({
                categories: data.categories || [],
                refreshCategoriesDisabled: false
            });
        }.bind(this));
    };

    onInputChange = (event) => {
        var newStateObject = {};
        newStateObject[event.target.name] = event.target.value;
        this.props.setFormStateListing(newStateObject);
    };

    onImageSelected = (image, selectedImageIds) => {
        this.props.setFormStateListing({
            imageId: image.id
        });
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
            />
        );
    };

    renderVariationSpecificFields = () => {
        if (this.props.variationsDataForProduct.length == 0) {
            return <SimpleProduct
                setFormStateListing={this.props.setFormStateListing}
                currency={this.state.currency}
                product={this.props.product}
                price={this.props.price}
            />;
        }

        return <VariationPicker
            variationsDataForProduct={this.props.variationsDataForProduct}
            variationFormState={this.props.variations}
            setFormStateListing={this.props.setFormStateListing}
            currency={this.state.currency}
            fetchVariations={this.props.fetchVariations}
            product={this.props.product}
            attributeNames={this.props.product.attributeNames}
        />
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
                    />
                </div>
            </label>
            <label>
                <span className={"inputbox-label"}>Brand</span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name="brand"
                        value={this.props.brand}
                        onChange={this.onInputChange}
                    />
                </div>
            </label>
            <label>
                <span className={"inputbox-label"}>Category</span>
                <div className={"order-inputbox-holder"}>
                    <CategorySelect
                        accountId={this.props.accountId}
                        categories={this.state.categories}
                        disabled={this.state.refreshCategoriesDisabled}
                        getSelectCallHandler={this.props.getSelectCallHandler}
                        selectedCategory={this.props.category}
                    />
                </div>
                <RefreshIcon
                    onClick={this.refreshCategories}
                    disabled={this.state.refreshCategoriesDisabled}
                />
            </label>
        </div>;
    }
}

export default Shopify;
