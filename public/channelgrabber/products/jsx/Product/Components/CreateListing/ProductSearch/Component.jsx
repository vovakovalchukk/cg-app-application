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
                renderCreateListingPopup: () => {}
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
            searchQuery: Selector(state, 'search')
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
