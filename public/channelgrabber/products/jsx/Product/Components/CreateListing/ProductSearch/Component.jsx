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
        renderForm: function() {
            return <form>
                <Field name="search" component={this.renderInputComponent} displayTitle={"Enter a UPC, EAN, ISBN, part number or a product name"}/>
            </form>
        },
        renderInputComponent: function(field) {
            return <label className="input-container">
                <span className={"inputbox-label"}>{field.displayTitle}</span>
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
            return <span className="search-product-container">
                {this.renderProductTitle(product)}
                {this.renderProductImage(product)}
                {this.renderProductItemSpecifics(product)}
            </span>
        },
        renderProductTitle: function (product) {
            return <span className="search-product-title">
                {product.title}
            </span>;
        },
        renderProductImage(product) {
            if (!product.imageUrl) {
                return null;
            }
            return <span className="search-product-image-container">
                <img
                    src={product.imageUrl}
                    className="search-product-image"
                />
            </span>;
        },
        renderProductItemSpecifics(product) {
            // todo - implement this
            return null;
        },
        render: function() {
            return (
                <Container
                    initiallyActive={true}
                    className="editor-popup product-create-listing"
                    closeOnYes={false}
                    headerText={"Create a listing"}
                    yesButtonText={"Search"}
                    noButtonText="Enter details manually"
                    onYesButtonPressed={this.fetchSearchResults}
                    onNoButtonPressed={() => {}}
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
