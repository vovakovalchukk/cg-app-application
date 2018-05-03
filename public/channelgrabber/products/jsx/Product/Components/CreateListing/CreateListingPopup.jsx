define([
    'react',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/Input',
    'Common/Components/TextArea',
    'Common/Components/Select',
    'Common/Components/ImagePicker',
    './Actions/CreateListings/Actions',
    './Components/ChannelForms',
    './Components/CategoryForms',
    './Components/CreateListing/ProductIdentifiers',
    './Components/CreateListing/Dimensions',
    './Components/CreateListing/ProductPrice'
], function(
    React,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
    Input,
    TextArea,
    Select,
    ImagePicker,
    Actions,
    ChannelForms,
    CategoryForms,
    ProductIdentifiers,
    Dimensions,
    ProductPrice
) {
    "use strict";

    var Field = ReduxForm.Field;
    var FormSection = ReduxForm.FormSection;

    var CreateListingPopup = React.createClass({
        getDefaultProps: function() {
            return {
                product: {},
                accounts: [],
                categories: [],
                conditionOptions: [],
                categoryTemplates: {},
                variationsDataForProduct: {},
                initialDimensions: {},
                accountsData: {},
                initialProductPrices: {}
            }
        },
        componentDidMount: function () {
            this.props.loadInitialValues(this.props.product, this.props.variationsDataForProduct, this.props.accounts);
        },
        renderForm: function() {
            return <form>
                <span className="heading-large">Listing information</span>
                <Field name="title" component={this.renderInputComponent} displayTitle={"Listing Title:"}/>
                <Field name="description" component={this.renderTextAreaComponent} displayTitle={"Description:"}/>
                <Field name="brand" component={this.renderInputComponent} displayTitle={"Brand (if applicable):"}/>
                <Field name="condition" component={this.renderSelectComponent} displayTitle={"Item Condition:"} options={this.props.conditionOptions}/>
                <Field name="imageId" component={this.renderImagePickerField}/>
                <FormSection
                    name="channel"
                    component={ChannelForms}
                    accounts={this.props.accounts}
                    categoryTemplates={this.props.categoryTemplates}
                    product={this.props.product}
                    variationsDataForProduct={this.props.variationsDataForProduct}
                />
                <FormSection
                    name="category"
                    component={CategoryForms}
                    accounts={this.props.accounts}
                    categoryTemplates={this.props.categoryTemplates}
                />
                {this.renderProductIdentifiers()}
                {this.renderDimensions()}
                {this.renderProductPrices()}
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
        renderTextAreaComponent: function(field) {
            return <label className="input-container">
                <span className={"inputbox-label"}>{field.displayTitle}</span>
                <div className={"order-inputbox-holder"}>
                    <TextArea
                        name={field.input.name}
                        value={field.input.value}
                        onChange={this.onInputChange.bind(this, field.input)}
                        className={"textarea-description"}
                    />
                </div>
            </label>;
        },
        renderSelectComponent: function(field) {
            return <label className="input-container">
                <span className={"inputbox-label"}>{field.displayTitle}</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        autoSelectFirst={false}
                        onOptionChange={this.onSelectOptionChange.bind(this, field.input)}
                        options={field.options}
                        selectedOption={this.findSelectedOption(field.input.value, field.options)}
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
            return (<label className="input-container">
                <span className={"inputbox-label"}>Images:</span>
                {this.renderImagePicker(field)}
            </label>);
        },
        renderImagePicker: function (field) {
            if (this.props.product.images.length == 0) {
                return (
                    <p className="react-image-picker main-image-picker">No images available</p>
                );
            }
            return (
                <ImagePicker
                    className={"main-image-picker"}
                    name={field.input.name}
                    multiSelect={false}
                    images={this.props.product.images}
                    onImageSelected={this.onImageSelected.bind(this, field.input)}
                />
            );
        },
        onImageSelected: function(input, selectedImage, selectedImageIds) {
            input.onChange(selectedImageIds);
        },
        renderProductIdentifiers: function() {
            return (<span>
                <span className="heading-large heading-table">Product Identifiers</span>
                <ProductIdentifiers
                    variationsDataForProduct={this.props.variationsDataForProduct}
                    product={this.props.product}
                    attributeNames={this.props.product.attributeNames}
                />
            </span>);
        },
        renderDimensions: function() {
            return (<span>
                <span className="heading-large heading-table">Dimensions</span>
                <Dimensions
                    variationsDataForProduct={this.props.variationsDataForProduct}
                    product={this.props.product}
                    attributeNames={this.props.product.attributeNames}
                    change={this.props.change}
                    initialDimensions={this.props.initialDimensions}
                />
            </span>);
        },
        renderProductPrices: function() {
            return (<span>
                <span className="heading-large heading-table">Price</span>
                <ProductPrice
                    variationsDataForProduct={this.props.variationsDataForProduct}
                    product={this.props.product}
                    attributeNames={this.props.product.attributeNames}
                    change={this.props.change}
                    accounts={this.getSelectedAccountsData()}
                    initialPrices={this.props.initialProductPrices}
                />
            </span>);
        },
        getSelectedAccountsData: function() {
            var accounts = [];
            this.props.accounts.map(function(accountId) {
                accounts.push(this.props.accountsData[accountId]);
            }.bind(this));
            return accounts;
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
        keepDirtyOnReinitialize: true,
        onSubmit: function(values, dispatch, props) {
            /** @TODO: this will be handled by LIS-159. */
            console.log(values);
        },
    })(CreateListingPopup);

    var mapStateToProps = function(state) {
        return {
            initialValues: state.initialValues,
            initialDimensions: state.initialValues.dimensions ? Object.assign(state.initialValues.dimensions) : {},
            initialProductPrices: state.initialValues.prices ? Object.assign(state.initialValues.prices) : {}
        };
    };

    var mapDispatchToProps = function(dispatch) {
        return {
            submitForm: function() {
                dispatch(ReduxForm.submit("createListing"));
            },
            loadInitialValues: function(product, variationData, accounts) {
                dispatch(Actions.loadInitialValues(product, variationData, accounts));
            }
        };
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(CreateListingPopup);
});
