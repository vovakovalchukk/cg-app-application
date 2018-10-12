import React from 'react';
import {connect} from 'react-redux';
import {Field, reduxForm, formValueSelector} from 'redux-form';
import Container from 'Common/Components/Container';
import Input from 'Common/Components/Input';
import Actions from './Actions/Actions';

const Selector = formValueSelector('productSearch');

class ProductSearchComponent extends React.Component {
    static defaultProps = {
        accountId: 0,
        createListingData: {},
        renderCreateListingPopup: () => {},
        onCreateListingClose: () => {},
        products: {},
        isFetching: false,
        defaultProductImage: ''
    };

    state = {
        selectedProduct: {}
    };

    renderForm = () => {
        return <form className="search-product-form" onSubmit={this.onFormSubmit}>
            <Field
                name="search"
                component={this.renderInputComponent}
                displayTitle={"Enter a UPC, EAN, ISBN or a product name"}
            />
            {this.renderSearchButton()}
            {this.renderEnterDetailsManuallyButton()}
        </form>
    };

    onFormSubmit = (event) => {
        event.preventDefault();
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

    renderEnterDetailsManuallyButton = () => {
        return <div
            className="button container-btn no"
            onClick={this.props.renderCreateListingPopup.bind(this, this.props.createListingData)}
        >
            Enter details manually
        </div>;
    };

    renderInputComponent = (field) => {
        return <label className="input-container">
            <span className={"inputbox-label search-label-container"}>{field.displayTitle}</span>
            <div className={"order-inputbox-holder"}>
                <Input
                    name={field.input.name}
                    value={field.input.value}
                    onChange={this.onInputChange.bind(this, field.input)}
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
        this.setState({
            selectedProduct: {}
        });
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
        return <div
            className={this.getProductContainerClassName(product)}
            onClick={this.toggleProductSelection.bind(this, product)}
        >
            {this.renderProductTitle(product)}
            <span className="search-product-details-container">
                {this.renderProductImage(product)}
                {this.renderProductItemSpecifics(product)}
            </span>
        </div>
    };

    getProductContainerClassName = (product) => {
        let className = "search-product-container";
        if (product.epid === this.state.selectedProduct.epid) {
            return className + ' selected';
        }
        return className;
    };

    toggleProductSelection = (product) => {
        if (product.epid === this.state.selectedProduct.epid) {
            this.setState({
                selectedProduct: {}
            });
            return;
        }

        this.setState({
            selectedProduct: Object.assign(product, {
                epidAccountId: this.props.accountId
            })
        });
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

    proceedWithSelectedProduct = () => {
        let product = this.state.selectedProduct;

        if (Object.keys(product).length === 0) {
            return null;
        }

        this.props.renderCreateListingPopup(Object.assign(this.props.createListingData, {
            selectedProductDetails: product
        }));
    };

    render() {
        return (
            <Container
                initiallyActive={true}
                className="product-search-container"
                closeOnYes={false}
                headerText={"Create a listing"}
                yesButtonText={"Select"}
                noButtonText={"Cancel"}
                onYesButtonPressed={this.proceedWithSelectedProduct}
                onBackButtonPressed={this.props.onBackButtonPressed.bind(this, this.props.createListingData.product)}
                onNoButtonPressed={this.props.onCreateListingClose}
                yesButtonDisabled={Object.keys(this.state.selectedProduct).length === 0}
            >
                {this.renderForm()}
                {this.renderSearchResults()}
            </Container>
        );
    }
}

ProductSearchComponent = reduxForm({
    form: "productSearch",
    onSubmit: function(values, dispatch, props) {
        props.renderCreateListingPopup(props.createListingData)
    },
})(ProductSearchComponent);

const mapStateToProps = function(state) {
    return {
        searchQuery: Selector(state, 'search'),
        products: state.products.products,
        isFetching: state.products.isFetching
    };
};

const mapDispatchToProps = function(dispatch) {
    return {
        fetchSearchResults: function(accountId, searchQuery) {
            dispatch(Actions.fetchSearchResults(accountId, searchQuery, dispatch));
        }
    };
};

ProductSearchComponent = connect(mapStateToProps, mapDispatchToProps)(ProductSearchComponent);

export default ProductSearchComponent;

