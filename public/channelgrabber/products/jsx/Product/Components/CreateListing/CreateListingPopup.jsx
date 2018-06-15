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
    './Components/CreateListing/ProductPrice',
    './Components/CreateListing/SubmissionTable',
    './Validators'
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
    ProductPrice,
    SubmissionTable,
    Validators
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
                initialProductPrices: {},
                defaultCurrency: null,
                accountDefaultSettings: {},
                submissionStatuses: {},
                onCreateListingClose: function() {}
            }
        },
        componentDidMount: function () {
            this.props.loadInitialValues();
        },
        renderForm: function() {
            return <form>
                <span className="heading-large">Listing information</span>
                <Field name="title" component={this.renderInputComponent} displayTitle={"Listing Title:"}/>
                <Field name="description" component={this.renderTextAreaComponent} displayTitle={"Description:"}/>
                <Field name="brand" component={this.renderInputComponent} displayTitle={"Brand (if applicable):"}/>
                <Field name="condition" component={this.renderSelectComponent} displayTitle={"Item Condition:"} options={this.props.conditionOptions} validate={Validators.required} />
                <Field name="imageId" component={this.renderImagePickerField} validate={Validators.required} />
                {this.renderChannelFormInputs()}
                {this.renderCategoryFormInputs()}
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
                        className={Validators.shouldShowError(field) ? 'error' : null}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
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
                        className={"textarea-description " + (Validators.shouldShowError(field) ? 'error' : '')}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
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
                        className={Validators.shouldShowError(field) ? 'error' : null}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
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
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
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
                    name={field.input.name}
                    multiSelect={false}
                    images={this.props.product.images}
                    onImageSelected={this.onImageSelected.bind(this, field.input)}
                    className={Validators.shouldShowError(field) ? 'error' : null}
                />
            );
        },
        onImageSelected: function(input, selectedImage, selectedImageIds) {
            input.onChange(selectedImageIds);
            input.onBlur(selectedImageIds);
        },
        renderChannelFormInputs: function() {
            return <FormSection
                name="channel"
                component={ChannelForms}
                accounts={this.props.accounts}
                categoryTemplates={this.props.categoryTemplates}
                product={this.props.product}
                variationsDataForProduct={this.props.variationsDataForProduct}
                currency={this.props.defaultCurrency}
            />;
        },
        renderCategoryFormInputs: function() {
            return <FormSection
                name="category"
                component={CategoryForms}
                accounts={this.props.accounts}
                categoryTemplates={this.props.categoryTemplates}
                product={this.props.product}
            />;
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
                    accounts={this.getSelectedAccountsData()}
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
                    currency={this.props.defaultCurrency}
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
        renderSubmissionTable: function () {
            return (<span>
                <span className="heading-large heading-table">Creation status</span>
                <SubmissionTable
                    accounts={this.formatAccountDataForSubmissionTable()}
                    categoryTemplates={this.props.categoryTemplates}
                    statuses={this.props.submissionStatuses}
                />
            </span>);
        },
        formatAccountDataForSubmissionTable: function() {
            var accounts = {};
            this.props.accounts.forEach(accountId => {
                accounts[accountId] = this.props.accountsData[accountId]
            });
            return accounts;
        },
        isSubmitButtonDisabled: function () {
            return this.props.submissionStatuses.inProgress;
        },
        render: function() {
            var isSubmitButtonDisabled = this.isSubmitButtonDisabled();
            return (
                <Container
                    initiallyActive={true}
                    className="editor-popup product-create-listing"
                    closeOnYes={false}
                    headerText={"Create a listing"}
                    yesButtonText={isSubmitButtonDisabled ? "Submitting..." : "Submit"}
                    noButtonText="Cancel"
                    onYesButtonPressed={this.props.submitForm}
                    onNoButtonPressed={this.props.onCreateListingClose}
                    onBackButtonPressed={this.props.onBackButtonPressed.bind(this, this.props.product)}
                    yesButtonDisabled={isSubmitButtonDisabled}
                >
                    {this.renderForm()}
                    {this.renderSubmissionTable()}
                </Container>
            );
        }
    });

    CreateListingPopup = ReduxForm.reduxForm({
        form: "createListing",
        enableReinitialize: true,
        // This is required to make the images in the variation table show correctly
        keepDirtyOnReinitialize: true,
        onSubmit: function(values, dispatch, props) {
            dispatch(Actions.submitListingsForm(dispatch, values, props));
        },
    })(CreateListingPopup);

    var mapStateToProps = function(state) {
        return {
            initialValues: state.initialValues,
            initialDimensions: state.initialValues.dimensions ? Object.assign(state.initialValues.dimensions) : {},
            initialProductPrices: state.initialValues.prices ? Object.assign(state.initialValues.prices) : {},
            submissionStatuses: JSON.parse(JSON.stringify(state.submissionStatuses))
        };
    };

    var mapDispatchToProps = function(dispatch, props) {
        return {
            submitForm: function() {
                dispatch(ReduxForm.submit("createListing"));
            },
            loadInitialValues: function() {
                dispatch(
                    Actions.loadInitialValues(
                        props.product,
                        props.variationsDataForProduct,
                        props.accounts,
                        props.accountDefaultSettings,
                        props.accountsData,
                        props.categoryTemplates
                    )
                );
            }
        };
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(CreateListingPopup);
});
