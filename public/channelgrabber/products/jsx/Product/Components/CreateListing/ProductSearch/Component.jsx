define([
    'react',
    'redux',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/Input',
    './Actions/Actions'
], function(
    React,
    Redux,
    ReactRedux,
    ReduxForm,
    Container,
    Input,
    Actions
) {
    const Field = ReduxForm.Field;
    const Selector = ReduxForm.formValueSelector('productSearch');

    let ProductSearchComponent = React.createClass({
        getDefaultProps: function() {
            return {
                accountId: 0,
                createListingData: {},
                renderCreateListingPopup: () => {},
                products: {}
            };
        },
        getInitialState: function() {
            return {
                selectedProduct: {}
            }
        },
        renderForm: function() {
            return <form className="search-product-form" onSubmit={this.onFormSubmit}>
                <Field name="search" component={this.renderInputComponent} displayTitle={"Enter a UPC, EAN, ISBN, part number or a product name"}/>
                {this.renderSearchButton()}
                {this.renderEnterDetailsManuallyButton()}
            </form>
        },
        onFormSubmit: function (event) {
            event.preventDefault();
        },
        renderSearchButton: function() {
            return <div
                className="button container-btn yes search-button"
                onClick={this.fetchSearchResults}
            >
                Search
            </div>;
        },
        renderEnterDetailsManuallyButton: function() {
            return <div
                className="button container-btn no"
                onClick={this.props.renderCreateListingPopup.bind(this, this.props.createListingData)}
            >
                Enter details manually
            </div>;
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
                onClick={this.markProductAsSelected.bind(this, product)}
            >
                {this.renderProductTitle(product)}
                <span className="search-product-details-container">
                    {this.renderProductImageAndSelectButton(product)}
                    {this.renderProductItemSpecifics(product)}
                </span>
            </div>
        },
        getProductContainerClassName: function(product) {
            let className = "search-product-container";
            if (product.epid === this.state.selectedProduct.epid) {
                return className + ' selected';
            }
            return className;
        },
        markProductAsSelected: function (product) {
            if (product.epid === this.state.selectedProduct.epid) {
                this.setState({
                    selectedProduct: {}
                });
                return;
            }

            this.setState({
                selectedProduct: product
            });
        },
        renderProductTitle: function (product) {
            return <span className="search-product-title" title={product.title}>
                {product.title}
            </span>;
        },
        renderProductImageAndSelectButton(product) {
            return <span className="search-product-image-select-container">
                <span className="search-product-image-container">
                    {this.renderProductImage(product)}
                </span>
                <div
                    className="button container-btn yes"
                    onClick={this.selectProduct.bind(this, product)}
                >
                    Select
                </div>
            </span>;
        },
        renderProductImage(product) {
            if (!product.imageUrl) {
                return null;
            }
            return <img
                src={product.imageUrl}
                className="search-product-image"
            />;
        },
        renderProductItemSpecifics(product) {
            if (!product.itemSpecifics || Object.keys(product.itemSpecifics).length === 0) {
                return null;
            }

            let itemSpecifics = Object.keys(product.itemSpecifics).map(name => {
                let value = product.itemSpecifics[name];
                return <span className="search-product-item-specific">
                    {name + ': ' + (Array.isArray(value) ? value.join(', ') : value)}
                </span>;
            });

            return <span className="search-product-item-specifics">
                {itemSpecifics}
            </span>;
        },
        selectProduct: function(product) {
            this.props.renderCreateListingPopup(Object.assign(this.props.createListingData, {
                selectedProductDetails: product
            }));
        },
        render: function() {
            return (
                <Container
                    initiallyActive={true}
                    className="product-search-container"
                    closeOnYes={false}
                    headerText={"Create a listing"}
                    onBackButtonPressed={() => {}}
                >
                    {this.renderForm()}
                    {this.renderSearchResults()}
                </Container>
            );
        }
    });

    ProductSearchComponent = ReduxForm.reduxForm({
        form: "productSearch",
        onSubmit: function(values, dispatch, props) {
            props.renderCreateListingPopup(props.createListingData)
        },
    })(ProductSearchComponent);

    const mapStateToProps = function(state) {
        return {
            searchQuery: Selector(state, 'search'),
            products: state.products
        };
    };

    const mapDispatchToProps = function(dispatch) {
        return {
            fetchSearchResults: function(accountId, searchQuery) {
                dispatch(Actions.fetchSearchResults(accountId, searchQuery, dispatch));
            }
        };
    };

    ProductSearchComponent = ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductSearchComponent);

    return ProductSearchComponent;
});
