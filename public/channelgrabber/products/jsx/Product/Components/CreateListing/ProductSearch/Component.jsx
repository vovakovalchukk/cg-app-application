define([
    'react',
    'redux',
    'react-redux',
    'redux-form',
    'Common/Components/Input',
    'Common/Components/Select',
    '../Actions/CreateListings/Actions',
    './AssignedProductsTable'
], function(
    React,
    Redux,
    ReactRedux,
    ReduxForm,
    Input,
    Select,
    CreateListingActions,
    AssignedProductsTable
) {
    const Field = ReduxForm.Field;
    const Selector = ReduxForm.formValueSelector('productSearch');

    let ProductSearchComponent = React.createClass({
        getDefaultProps: function() {
            return {
                accountId: null,
                products: {},
                isFetching: false,
                defaultProductImage: '',
                mainProduct: {},
                variationsDataForProduct: {},
                selectedProducts: {},
                attributeNames: {},
                attributeNameMap: {}
            }
        },
        getInitialState: function() {
            return {
                selectedProduct: {}
            }
        },
        renderForm: function() {
            return <form className="search-product-form" onSubmit={this.onFormSubmit}>
                <Field
                    name="search"
                    component={this.renderInputComponent}
                    displayTitle={"Search our database of products to find your product and pre-fill your listing information"}
                    placeholder={"Enter a UPC, EAN, ISBN or a product name"}
                />
                {this.renderSearchButton()}
                <span style={{ color: "#444444"}}>{"Skip this section if you wish to fill in your product information manually instead."}</span>
            </form>
        },
        onFormSubmit: function (event) {
            event.preventDefault();
        },
        renderSearchButton: function() {
            return <div
                className={this.getSearchButtonClassName()}
                onClick={this.fetchSearchResults}
            >
                {this.props.isFetching ? "Searching..." : "Search"}
            </div>;
        },
        getSearchButtonClassName: function() {
            return "button container-btn yes search-button" + (this.props.isFetching ? ' disabled' : '');
        },
        renderInputComponent: function(field) {
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
        },
        onInputChange: function(input, value) {
            input.onChange(value);
        },
        fetchSearchResults: function() {
            if (this.props.isFetching) {
                return null;
            }
            this.setState({
                selectedProduct: {}
            });
            this.props.fetchSearchResults(this.props.accountId, this.props.searchQuery);
        },
        renderSearchResults: function() {
            if (Object.keys(this.props.products).length === 0) {
                return null;
            }

            let products = Object.keys(this.props.products).map(productKey => {
                let product = this.props.products[productKey];
                return this.renderProduct(product);
            });

            return <span className="search-products-container scrollhint">
                {products}
            </span>
        },
        renderProduct: function (product) {
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
        },
        renderProductTitle: function (product) {
            return <span className="search-product-title" title={product.title}>
                {product.title}
            </span>;
        },
        renderProductImage: function(product) {
            let imageUrl = product.imageUrl ? product.imageUrl : this.props.defaultProductImage;
            return <img
                src={imageUrl}
                className="search-product-image"
            />;
        },
        renderProductItemSpecifics: function(product) {
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
        },
        getItemSpecificsForProduct: function(product) {
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
        },
        renderAssignSelect: function (product) {
            let options = [];
            this.props.variationsDataForProduct.forEach(function(variation) {
                options.push({
                    name: this.buildOptionName(variation),
                    value: variation.sku,
                    disabled: !!this.props.selectedProducts[variation.sku]
                });
            }.bind(this));

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
        },
        buildOptionName: function(variation) {
            let name = variation.sku;
            Object.keys(variation.attributeValues).forEach(function(attributeName) {
                name += ' - ' + variation.attributeValues[attributeName];
            });
            return name;
        },
        findSelectedOptionForProduct: function (product) {
            let selectedSku = '';
            Object.keys(this.props.selectedProducts).map(function(sku) {
                let searchProduct = this.props.selectedProducts[sku];
                if (searchProduct.epid === product.epid) {
                    selectedSku = sku;
                }
            }.bind(this));

            const variation = this.props.variationsDataForProduct.find(function(variation) {
                return variation.sku == selectedSku;
            });

            return {
                name: variation ? this.buildOptionName(variation) : selectedSku,
                value: selectedSku
            };
        },
        onProductAssign: function(searchProduct, selectedSku) {
            this.props.assignSearchProductToCgProduct(searchProduct, selectedSku.value);
        },
        renderAssignedProductsTable: function () {
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
        },
        render: function() {
            return <span>
                {this.renderForm()}
                {this.renderSearchResults()}
                {this.renderAssignedProductsTable()}
            </span>;
        }
    });

    ProductSearchComponent = ReduxForm.reduxForm({
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
            selectedProducts: productSearch.selectedProducts
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

    ProductSearchComponent = ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductSearchComponent);

    return ProductSearchComponent;
});
