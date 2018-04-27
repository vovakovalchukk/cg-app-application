define([
    'react',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/Input',
    'Common/Components/Select',
], function(
    React,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
    Input,
    Select
) {
    "use strict";

    var Field = ReduxForm.Field;

    var CreateListingPopup = React.createClass({
        getDefaultProps: function() {
            return {
                product: {},
                accounts: [],
                categories: [],
                conditionOptions: []
            }
        },
        renderForm: function() {
            return <form onSubmit={this.props.handleSubmit}>
                <Field name="title" component={this.renderInputComponent.bind(this, "Listing Title:")}/>
                <Field name="description" component={this.renderInputComponent.bind(this, "Description:")}/>
                <Field name="brand" component={this.renderInputComponent.bind(this, "Brand:")}/>
                <Field name="condition" component={this.renderSelectComponent.bind(this, "Item Condition:", this.props.conditionOptions)}/>
            </form>
        },
        renderInputComponent: function(title, field) {
            return <label>
                <span className={"inputbox-label"}>{title}</span>
                <div className={"order-inputbox-holder"}>
                    <Input
                        name={field.input.name}
                        value={field.input.value}
                        onChange={this.onInputChange.bind(this, field.input)}
                    />
                </div>
            </label>;
        },
        renderSelectComponent: function(title, options, field) {
            return <label>
                <span className={"inputbox-label"}>{title}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        autoSelectFirst={false}
                        onOptionChange={this.onSelectOptionChange.bind(this, field.input)}
                        options={options}
                        selectedOption={this.findSelectedOption(field.input.value, options)}
                    />
                </div>
            </label>;
        },
        findSelectedOption: function(value, options) {
            var selectedOption = {
                name: '',
                value: ''
            };
            options.forEach(function(option) {
                if (option.value == value) {
                    selectedOption = option;
                }
            });
            return selectedOption;
        },
        onSelectOptionChange: function(input, option) {
            this.onInputChange(input, option.value);
        },
        onInputChange: function(input, value) {
            input.onChange(value);
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
