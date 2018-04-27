define([
    'react',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/Input',
    'Common/Components/Select',
    'Common/Components/ImagePicker',
    './Actions/CreateListings/Actions'
], function(
    React,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
    Input,
    Select,
    ImagePicker,
    Actions
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
        componentDidMount: function () {
            this.props.loadInitialValues(this.props.product);
        },
        renderForm: function() {
            return <form onSubmit={this.props.handleSubmit}>
                <Field name="title" component={this.renderInputComponent.bind(this, "Listing Title:")}/>
                <Field name="description" component={this.renderInputComponent.bind(this, "Description:")}/>
                <Field name="brand" component={this.renderInputComponent.bind(this, "Brand (if applicable):")}/>
                <Field name="condition" component={this.renderSelectComponent.bind(this, "Item Condition:", this.props.conditionOptions)}/>
                <Field name="imageIds" component={this.renderImagePickerField}/>
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
        renderImagePickerField: function(field) {
            return (<label>
                <span className={"inputbox-label"}>Images:</span>
                {this.renderImagePicker(field)}
            </label>);
        },
        renderImagePicker: function (field) {
            if (this.props.product.images.length == 0) {
                return (
                    <p>No images available</p>
                );
            }
            return (
                <ImagePicker
                    name={field.input.name}
                    multiSelect={true}
                    images={this.props.product.images}
                    onImageSelected={this.onImageSelected.bind(this, field.input)}
                />
            );
        },
        onImageSelected: function(input, selectedImage, selectedImageIds) {
            input.onChange(selectedImageIds);
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
        enableReinitialize: true,
        onSubmit: function(values, dispatch, props) {
            console.log(values);
        },
    })(CreateListingPopup);

    var mapStateToProps = function(state) {
        return {
            initialValues: state.initialValues
        };
    };

    var mapDispatchToProps = function(dispatch) {
        return {
            submitForm: function() {
                dispatch(ReduxForm.submit("createListing"));
            },
            loadInitialValues: function(product) {
                dispatch(Actions.loadInitialValues(product));
            }
        };
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(CreateListingPopup);
});
