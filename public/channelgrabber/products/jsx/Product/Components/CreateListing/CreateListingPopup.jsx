define([
    'react',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/Input'
], function(
    React,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
    Input
) {
    "use strict";

    var Field = ReduxForm.Field;

    var CreateListingPopup = React.createClass({
        getDefaultProps: function() {
            return {
                product: {},
                accounts: [],
                categories: []
            }
        },
        renderForm: function() {
            return <form onSubmit={this.props.handleSubmit}>
                <Field name="title" component={this.renderTitleComponent}/>
                <Field name="description" component={this.renderDescriptionComponent}/>
            </form>
        },
        onInputChange: function(input, value) {
            input.onChange(value);
        },
        renderTitleComponent: function(field) {
            return <label>
                <span className={"inputbox-label"}>Listing Title:</span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={field.input.name}
                        value={field.input.value}
                        onChange={this.onInputChange.bind(this, field.input)}
                    />
                </div>
            </label>;
        },
        renderDescriptionComponent: function(field) {
            return <label>
                <span className={"inputbox-label"}>Description:</span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={field.input.name}
                        value={field.input.value}
                        onChange={this.onInputChange.bind(this, field.input)}
                    />
                </div>
            </label>;
        },
        render: function() {
            return (
                <Container
                    initiallyActive={true}
                    className="editor-popup product-create-listing"
                    closeOnYes={false}
                    headerText={"Create a listing"}
                    yesButtonText="Submit"
                    noButtonText="Cancel"
                    onYesButtonPressed={this.props.submitForm}
                >
                    {this.renderForm()}
                </Container>
            );
        }
    });

    CreateListingPopup = ReduxForm.reduxForm({
        form: "createListing",
        initialValues: {},
        onSubmit: function(values, dispatch, props) {
            console.log(values);
        },
    })(CreateListingPopup);

    var mapStateToProps = function(state) {
        return {};
    };

    var mapDispatchToProps = function(dispatch) {
        return {
            submitForm: function() {
                dispatch(ReduxForm.submit("createListing"));
            }
        };
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(CreateListingPopup);
});
