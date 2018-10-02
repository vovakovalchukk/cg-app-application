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
    './Validators',
    './ProductSearch/Component',
    'Common/Components/SectionedContainer',
    'Common/SectionData'
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
    Validators,
    ProductSearch,
    SectionedContainer,
    SectionData
) {
    "use strict";

    const Field = ReduxForm.Field;
    const FormSection = ReduxForm.FormSection;
    const FormSelector = ReduxForm.formValueSelector('createListing');

    let CreateListingPopup = React.createClass({
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
                onCreateListingClose: function() {},
                massUnit: null,
                lengthUnit: null,
                selectedProductDetails: {},
                productSearchActive: false,
                productSearch: {},
                defaultProductImage: ''
            }
        },
        componentDidMount: function () {
            this.props.fetchCategoryTemplateDependentFieldValues();
            this.props.loadInitialValues(this.findSearchAccountId());
        },
        componentWillUnmount: function() {
            this.props.revertToInitialValues();
        },
        componentDidUpdate: function() {
            if (this.isPbseRequired() && this.areAllVariationsAssigned()) {
                this.props.clearErrorFromProductSearch();
            }
        },
        findSearchAccountId: function() {
            let accountId = this.props.accounts.find(function(accountId) {
                let accountData = this.props.accountsData[accountId];
                return accountData.channel == 'ebay' && accountData.listingsAuthActive;
            }, this);

            return accountId > 0 ? accountId : null;
        },
        renderProductSearchComponent: function() {
            if (!this.shouldRenderProductSearchComponent()) {
                return null;
            }

            return <ProductSearch
                accountId={this.props.searchAccountId}
                mainProduct={this.props.product}
                variationsDataForProduct={this.props.variationsDataForProduct}
                clearSelectedProduct={this.props.clearSelectedProduct}
                variationImages={this.props.variationImages}
                defaultProductImage={this.props.defaultProductImage}
            />;
        },
        shouldRenderProductSearchComponent: function() {
            if (!this.props.productSearchActive) {
                return false;
            }

            if (this.props.product.variationCount > 1) {
                return false;
            }

            return !!this.props.searchAccountId;
        },
        renderForm: function() {
            return <form>
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
                        classNames={'u-width-300px'}
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
                categoryTemplates={this.props.categoryTemplates.categories}
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
                categoryTemplates={this.props.categoryTemplates.categories}
                product={this.props.product}
                variationsDataForProduct={this.props.variationsDataForProduct}
                fieldChange={this.props.change}
                resetSection={this.props.resetSection}
                selectedProductDetails={this.props.selectedProductDetails}
            />;
        },
        renderProductIdentifiers: function() {
            return (<span>
                <span className="heading-large heading-table">Product Identifiers</span>
                <ProductIdentifiers
                    variationsDataForProduct={this.props.variationsDataForProduct}
                    product={this.props.product}
                    attributeNames={this.props.product.attributeNames}
                    variationImages={this.props.variationImages}
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
                    massUnit={this.props.massUnit}
                    lengthUnit={this.props.lengthUnit}
                    variationImages={this.props.variationImages}
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
                    variationImages={this.props.variationImages}
                />
            </span>);
        },
        getSelectedAccountsData: function() {
            let accounts = [];
            this.props.accounts.map(function(accountId) {
                accounts.push(this.props.accountsData[accountId]);
            }.bind(this));
            return accounts;
        },
        renderSubmissionTable: function () {
            return (<span>
                <SubmissionTable
                    accounts={this.formatAccountDataForSubmissionTable()}
                    categoryTemplates={this.props.categoryTemplates.categories}
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
        areCategoryTemplatesFetching: function() {
            return this.props.categoryTemplates.isFetching;
        },
        validateProductAssignation: function(event) {
            if (this.isPbseRequired() && !this.areAllVariationsAssigned()) {
                event.preventDefault();
                this.addVariationErrorOnProductSearch();
                return;
            }

            if (this.props.productSearch.error) {
                this.props.clearErrorFromProductSearch();
            }
        },
        areAllVariationsAssigned: function() {
            return this.props.variationsDataForProduct.every(variation => {
                return !!(this.props.productSearch.selectedProducts[variation.sku]);
            });
        },
        isPbseRequired: function() {
            if (this.props.variationsDataForProduct.length === 1) {
                return false;
            }

            if (!this.props.categoryTemplates.categories) {
                return false;
            }

            return Object.values(this.props.categoryTemplates.categories).some(categoryTemplate => {
                return Object.values(categoryTemplate.accounts).some(category => {
                    return category.channel == 'ebay' && category.fieldValues && category.fieldValues.pbse && category.fieldValues.pbse.required;
                });
            });
        },
        addVariationErrorOnProductSearch: function() {
            this.props.addErrorOnProductSearch('You must assign a product to all your variations. This must be done because one of your selected eBay categories requires all the variations of your product to be mapped to existing products.');
        },
        buildSections: function() {
            const productSearchComponent = this.renderProductSearchComponent();

            const sections = [
                new SectionData('Listing Information', this.renderForm()),
                new SectionData('Listing creation status', this.renderSubmissionTable())
            ];

            if (productSearchComponent) {
                sections.unshift(this.buildProductSearchSectionData(productSearchComponent));
            }

            return sections;
        },
        buildProductSearchSectionData: function(productSearchComponent) {
            return new SectionData(
                'Search for your product',
                productSearchComponent,
                this.validateProductAssignation,
                this.isYesButtonDisabledForProductSearch()
            );
        },
        isYesButtonDisabledForProductSearch: function() {
            return this.props.categoryTemplates.isFetching;
        },
        submitForm: function() {
            if (this.isPbseRequired() && !this.areAllVariationsAssigned()) {
                this.addVariationErrorOnProductSearch();
                $('html, body').animate({
                    scrollTop: ($("a[name=section0]").offset().top)
                }, 200);
                return;
            }

            this.props.submitForm();
        },
        render: function() {
            const isSubmitButtonDisabled = this.isSubmitButtonDisabled();
            return <SectionedContainer
                sectionClassName={"editor-popup product-create-listing"}
                yesButtonText={isSubmitButtonDisabled ? "Submitting..." : "Submit"}
                noButtonText="Cancel"
                onYesButtonPressed={this.submitForm}
                onNoButtonPressed={this.props.onCreateListingClose}
                onBackButtonPressed={this.props.onBackButtonPressed.bind(this, this.props.product)}
                yesButtonDisabled={(isSubmitButtonDisabled || this.areCategoryTemplatesFetching())}
                sections={this.buildSections()}
            />;
        }
    });

    CreateListingPopup = ReduxForm.reduxForm({
        form: "createListing",
        enableReinitialize: true,
        // This is required to make the images in the variation table show correctly
        keepDirtyOnReinitialize: true,
        updateUnregisteredFields: true,
        onSubmit: function(values, dispatch, props) {
            dispatch(Actions.submitListingsForm(dispatch, values, props));
        },
    })(CreateListingPopup);

    const mapStateToProps = function(state) {
        return {
            initialValues: state.initialValues,
            initialDimensions: state.initialValues.dimensions ? Object.assign(state.initialValues.dimensions) : {},
            initialProductPrices: state.initialValues.prices ? Object.assign(state.initialValues.prices) : {},
            submissionStatuses: JSON.parse(JSON.stringify(state.submissionStatuses)),
            resetSection: ReduxForm.resetSection,
            categoryTemplates: state.categoryTemplates,
            productSearch: state.productSearch,
            variationImages: FormSelector(state, 'images')
        };
    };

    const mapDispatchToProps = function(dispatch, props) {
        return {
            submitForm: function() {
                dispatch(ReduxForm.submit("createListing"));
            },
            loadInitialValues: function(searchAccountId) {
                dispatch(
                    Actions.loadInitialValues(
                        props.product,
                        props.variationsDataForProduct,
                        props.accounts,
                        props.accountDefaultSettings,
                        props.accountsData,
                        props.categoryTemplates ? props.categoryTemplates.categories : {},
                        searchAccountId
                    )
                );
            },
            revertToInitialValues: function () {
                dispatch(Actions.revertToInitialValues());
            },
            fetchCategoryTemplateDependentFieldValues: function() {
                dispatch(Actions.fetchCategoryTemplateDependentFieldValues(props.categories, props.accountDefaultSettings, props.accountsData, dispatch));
            },
            clearSelectedProduct: function(sku) {
                dispatch(Actions.clearSelectedProduct(sku, props.variationsDataForProduct));
            },
            addErrorOnProductSearch: function(errorMessage) {
                dispatch(Actions.addErrorOnProductSearch(errorMessage));
            },
            clearErrorFromProductSearch: function() {
                dispatch(Actions.clearErrorFromProductSearch());
            }
        };
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(CreateListingPopup);
});
