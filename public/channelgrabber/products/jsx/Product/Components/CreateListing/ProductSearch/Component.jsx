define([
    'react',
    'redux',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/Input',
    'Common/Components/Select',
    '../Actions/CreateListings/Actions'
], function(
    React,
    Redux,
    ReactRedux,
    ReduxForm,
    Container,
    Input,
    Select,
    CreateListingActions
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
                selectedProducts: {}
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
                    displayTitle={"Enter a UPC, EAN, ISBN or a product name"}
                />
                {this.renderSearchButton()}
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

            return <span className="search-products-container">
                {products}
            </span>
        },
        renderProduct: function (product) {
            return <div
                className={this.getProductContainerClassName(product)}
                onClick={this.toggleProductSelection.bind(this, product)}
            >
                {this.renderProductTitle(product)}
                <span className="search-product-details-container">
                    {this.renderProductImage(product)}
                    {this.renderProductItemSpecifics(product)}
                </span>
                {this.renderAssignSelect(product)}
            </div>
        },
        getProductContainerClassName: function(product) {
            let className = "search-product-container";
            if (product.epid === this.state.selectedProduct.epid) {
                return className + ' selected';
            }
            return className;
        },
        toggleProductSelection: function (product) {
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
            var options = this.props.variationsDataForProduct.map(function(variation) {
                return {
                    name: variation.sku,
                    value: variation.sku
                };
            });

            return <span className="search-product-assign-select">
                <span>Assign to:</span>
                <Select
                    name="product-assign"
                    options={options}
                    autoSelectFirst={false}
                    title="Assign Product"
                    onOptionChange={this.onProductAssign.bind(this, product)}
                    filterable={true}
                />
            </span>;
        },
        onProductAssign: function(searchProduct, selectedSku) {
            this.props.assignSearchProductToCgProduct(searchProduct, selectedSku.value);
        },
        proceedWithSelectedProduct: function() {
            let product = this.state.selectedProduct;

            if (Object.keys(product).length === 0) {
                return null;
            }

            this.props.renderCreateListingPopup(Object.assign(this.props.createListingData, {
                selectedProductDetails: product
            }));
        },
        render: function() {
            return <span>
                    {this.renderForm()}
                    {this.renderSearchResults()}
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
