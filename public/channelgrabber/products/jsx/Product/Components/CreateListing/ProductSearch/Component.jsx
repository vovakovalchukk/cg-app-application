define([
    'react',
    'redux',
    'react-redux',
    'redux-form',
    'Common/Components/Input',
], function(
    React,
    Redux,
    ReactRedux,
    ReduxForm,
    Input
) {
    const Field = ReduxForm.Field;

    let ProductSearchComponent = React.createClass({
        getDefaultProps: function() {
            return {
                createListingData: {},
                renderCreateListingPopup: () => {}
            };
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
        render: function() {
            return <form>
                <span className="heading-large">Product search</span>
                <Field name="title" component={this.renderInputComponent} displayTitle={"Enter a UPC, EAN, ISBN, part number or a product name"}/>
            </form>
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
        };
    };

    const mapDispatchToProps = function(dispatch, props) {
        return {
        };
    };

    ProductSearchComponent = ReactRedux.connect(mapStateToProps, mapDispatchToProps)(ProductSearchComponent);

    return ProductSearchComponent;
});
