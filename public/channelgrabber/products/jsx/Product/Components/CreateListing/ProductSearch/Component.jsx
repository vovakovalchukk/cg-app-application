import React from 'react';
import {Field, reduxForm, formValueSelector} from 'redux-form';
import {connect} from 'react-redux';
import Input from 'Common/Components/Input';
import Select from 'Common/Components/Select';
import CreateListingActions from '../Actions/CreateListings/Actions';
import AssignedProductsTable from './AssignedProductsTable';

const Selector = formValueSelector('productSearch');

class ProductSearchComponent extends React.Component {
    static defaultProps = {
        accountId: null,
        products: {},
        isFetching: false,
        defaultProductImage: '',
        mainProduct: {},
        variationsDataForProduct: {},
        selectedProducts: {},
        attributeNames: {},
        attributeNameMap: {},
        errorMessage: false
    };

    renderForm = () => {
        return <form className="search-product-form" onSubmit={this.onFormSubmit}>
            <Field
                name="search"
                component={this.renderInputComponent}
                displayTitle={"Search our database of products to find your product and pre-fill your listing information"}
                placeholder={"Enter a UPC, EAN, ISBN or a product name"}
            />
            {this.renderSearchButton()}
            {this.renderSkipSectionMessage()}
        </form>
    };

    onFormSubmit = (event) => {
        event.preventDefault();
    };

    renderSkipSectionMessage = () => {
        return <span style={{ color: "#444444"}}>
            {"Skip this section if you wish to fill in your product information manually instead."}
        </span>;
    };

    renderSearchButton = () => {
        return <div
            className={this.getSearchButtonClassName()}
            onClick={this.fetchSearchResults}
        >
            {this.props.isFetching ? "Searching..." : "Search"}
        </div>;
    };

    getSearchButtonClassName = () => {
        return "button container-btn yes search-button" + (this.props.isFetching ? ' disabled' : '');
    };

    renderInputComponent = (field) => {
        return <label className="input-container">
            <span className={"inputbox-label search-label-container"}>{field.displayTitle}</span>
            <div className={"order-inputbox-holder"}>
                <Input
                    name={field.input.name}
                    value={field.input.value}
                    onChange={this.onInputChange.bind(this, field.input)}
                    placeholder={field.placeholder}
                />
            </div>
        </label>;
    };

    onInputChange = (input, value) => {
        input.onChange(value);
    };

    fetchSearchResults = () => {
        if (this.props.isFetching) {
            return null;
        }
        this.props.fetchSearchResults(this.props.accountId, this.props.searchQuery);
    };

    renderSearchResults = () => {
        if (Object.keys(this.props.products).length === 0) {
            return null;
        }

        let products = Object.keys(this.props.products).map(productKey => {
            let product = this.props.products[productKey];
            return this.renderProduct(product);
        });

        return <span className="search-products-container">
            {products}
        </span>
    };

    renderProduct = (product) => {
        return <span>
            <div className={'search-product-container'}>
                {this.renderProductTitle(product)}
                <span className="search-product-details-container">
                    {this.renderProductImage(product)}
                    {this.renderProductItemSpecifics(product)}
                </span>
                {this.renderAssignSelect(product)}
            </div>
        </span>
    };

    renderProductTitle = (product) => {
        return <span className="search-product-title" title={product.title}>
            {product.title}
        </span>;
    };

    renderProductImage = (product) => {
        let imageUrl = product.imageUrl ? product.imageUrl : this.props.defaultProductImage;
        return <img
            src={imageUrl}
            className="search-product-image"
        />;
    };

    renderProductItemSpecifics = (product) => {
        let productItemSpecifics = this.getItemSpecificsForProduct(product);
        if (!productItemSpecifics) {
            return null;
        }

        let itemSpecifics = Object.keys(productItemSpecifics).map(name => {
            let value = productItemSpecifics[name];
            return <tr>
                <td>{name}</td>
                <td>{Array.isArray(value) ? value.join(', ') : value}</td>
            </tr>;
        });

        return <table className="search-product-item-specifics">
            {itemSpecifics}
        </table>;
    };

    getItemSpecificsForProduct = (product) => {
        if (!product.itemSpecifics || Object.keys(product.itemSpecifics).length === 0) {
            return null;
        }

        let itemSpecifics = product.itemSpecifics;

        if (product.ean) {
            itemSpecifics["EAN"] = product.ean;
        }
        if (product.upc) {
            itemSpecifics["UPC"] = product.upc;
        }
        if (product.isbn) {
            itemSpecifics["ISBN"] = product.isbn;
        }
        if (product.mpn) {
            itemSpecifics["MPN"] = product.mpn;
        }

        return itemSpecifics;
    };

    renderAssignSelect = (product) => {
        let options = [];
        this.props.variationsDataForProduct.forEach(function(variation) {
            options.push({
                name: this.buildOptionName(variation),
                value: variation.id,
                disabled: !!this.props.selectedProducts[variation.id]
            });
        }, this);

        return <span className="search-product-assign-select">
            <span>Assign to:</span>
            <Select
                name="product-assign"
                options={options}
                autoSelectFirst={false}
                title="Assign Product"
                onOptionChange={this.onProductAssign.bind(this, product)}
                filterable={true}
                selectedOption={this.findSelectedOptionForProduct(product)}
            />
        </span>;
    };

    buildOptionName = (variation) => {
        let name = variation.sku;
        Object.keys(variation.attributeValues).forEach(function(attributeName) {
            name += ' - ' + variation.attributeValues[attributeName];
        });
        return name;
    };

    findSelectedOptionForProduct = (product) => {
        let selectedProductId = '';
        Object.keys(this.props.selectedProducts).map(function(id) {
            let searchProduct = this.props.selectedProducts[id];
            if (searchProduct.epid === product.epid) {
                selectedProductId = id;
            }
        }.bind(this));

        const variation = this.props.variationsDataForProduct.find(function(variation) {
            return variation.id == selectedProductId;
        });

        return {
            name: variation ? this.buildOptionName(variation) : selectedProductId,
            value: selectedProductId
        };
    };

    onProductAssign = (searchProduct, selectedProduct) => {
        this.props.assignSearchProductToCgProduct(searchProduct, selectedProduct.value);
    };

    renderAssignedProductsTable = () => {
        return <AssignedProductsTable
            selectedProducts={this.props.selectedProducts}
            variationsDataForProduct={this.props.variationsDataForProduct}
            product={this.props.mainProduct}
            attributeNames={this.props.mainProduct.attributeNames}
            attributeNameMap={this.props.mainProduct.attributeNameMap}
            clearSelectedProduct={this.props.clearSelectedProduct}
            variationImages={this.props.variationImages}
            defaultProductImage={this.props.defaultProductImage}
        />;
    };

    renderErrorMessage = () => {
        if (!this.props.errorMessage) {
            return null;
        }

        return <span className={'product-search-error-container'}>{this.props.errorMessage}</span>;
    };

    render() {
        return <span>
            {this.renderForm()}
            {this.renderSearchResults()}
            {this.renderAssignedProductsTable()}
            {this.renderErrorMessage()}
        </span>;
    }
}

ProductSearchComponent = reduxForm({
    form: "productSearch",
    onSubmit: function(values, dispatch, props) {
        props.renderCreateListingPopup(props.createListingData)
    },
})(ProductSearchComponent);

const mapStateToProps = function(state) {
    let productSearch = state.productSearch;
    return {
        searchQuery: Selector(state, 'search'),
        products: productSearch.products,
        isFetching: productSearch.isFetching,
        selectedProducts: productSearch.selectedProducts,
        errorMessage: productSearch.error
    };
};

const mapDispatchToProps = function(dispatch) {
    return {
        fetchSearchResults: function(accountId, searchQuery) {
            dispatch(CreateListingActions.fetchSearchResults(accountId, searchQuery, dispatch));
        },
        assignSearchProductToCgProduct: function(searchProduct, cgProduct) {
            dispatch(CreateListingActions.assignSearchProductToCgProduct(searchProduct, cgProduct));
        }
    };
};

ProductSearchComponent = connect(mapStateToProps, mapDispatchToProps)(ProductSearchComponent);

export default ProductSearchComponent;

