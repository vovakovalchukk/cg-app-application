import React, {useState} from 'react';
import {connect} from 'react-redux';
import {Field, FieldArray, FormSection, reduxForm, resetSection, submit, formValueSelector} from 'redux-form';
import Input from 'Common/Components/Input';
import TextArea from 'Common/Components/TextArea';
import Select from 'Common/Components/Select';
import ImagePicker from 'Common/Components/ImagePicker';
import Actions from './Actions/CreateListings/Actions';
import ChannelForms from './Components/ChannelForms';
import CategoryForms from './Components/CategoryForms';
import ProductIdentifiers from './Components/CreateListing/ProductIdentifiers';
import Dimensions from './Components/CreateListing/Dimensions';
import ProductPrice from './Components/CreateListing/ProductPrice';
import SubmissionTable from './Components/CreateListing/SubmissionTable';
import Validators from './Validators';
import ProductSearch from './ProductSearch/Component';
import SectionedContainer from 'Common/Components/SectionedContainer';
import SectionData from 'Common/SectionData';

const FormSelector = formValueSelector('createListing');

function submitForm (values, dispatch, props){
    dispatch(Actions.submitListingsForm(
        dispatch,
        values,
        props
    ));
}

class CreateListingPopup extends React.Component {
    static defaultProps = {
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
    };

    componentDidMount() {
        this.props.fetchCategoryTemplateDependentFieldValues();
        this.props.loadInitialValues(this.findSearchAccountId());
    }

    componentWillUnmount() {
        this.props.revertToInitialValues();
    }

    componentDidUpdate() {
        if (this.isPbseRequired() && this.areAllVariationsAssigned()) {
            this.props.clearErrorFromProductSearch();
        }
    }

    renderForm = () => {
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
    };

    findSearchAccountId = () => {
        let accountId = this.props.accounts.find(function(accountId) {
            let accountData = this.props.accountsData[accountId];
            return accountData.channel == 'ebay' && accountData.listingsAuthActive;
        }, this);

        return accountId > 0 ? accountId : null;
    };

    renderProductSearchComponent = () => {
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
    };

    shouldRenderProductSearchComponent = () => {
        if (!this.props.productSearchActive) {
            return false;
        }

        return !!this.props.searchAccountId;
    };

    renderInputComponent = (field) => {
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
    };

    renderTextAreaComponent = (field) => {
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
    };

    renderSelectComponent = (field) => {
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
    };

    findSelectedOption = (value, options) => {
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
    };

    onSelectOptionChange = (input, option) => {
        this.onInputChange(input, option.value);
    };

    onInputChange = (input, value) => {
        input.onChange(value);
    };

    renderImagePickerField = (field) => {
        return (<label className="input-container">
            <span className={"inputbox-label"}>Images:</span>
            {this.renderImagePicker(field)}
            {Validators.shouldShowError(field) && (
                <span className="input-error">{field.meta.error}</span>
            )}
        </label>);
    };

    renderImagePicker = (field) => {
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
    };

    onImageSelected = (input, selectedImage, selectedImageIds) => {
        input.onChange(selectedImageIds);
        input.onBlur(selectedImageIds);
    };

    renderChannelFormInputs = () => {
        return <FormSection
            name="channel"
            component={ChannelForms}
            accounts={this.props.accounts}
            categoryTemplates={this.props.categoryTemplates.categories}
            product={this.props.product}
            variationsDataForProduct={this.props.variationsDataForProduct}
            currency={this.props.defaultCurrency}
        />;
    };

    renderCategoryFormInputs = () => {
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
    };

    renderProductIdentifiers = () => {
        return (<span>
            <span className="heading-large heading-table">Product Identifiers</span>
            <ProductIdentifiers
                variationsDataForProduct={this.props.variationsDataForProduct}
                product={this.props.product}
                attributeNames={this.props.product.attributeNames}
                variationImages={this.props.variationImages}
            />
        </span>);
    };

    renderDimensions = () => {
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
    };

    renderProductPrices = () => {
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
    };

    getSelectedAccountsData = () => {
        let accounts = [];
        this.props.accounts.map(accountId => {
            accounts.push(this.props.accountsData[accountId]);
        });
        return accounts;
    };

    renderSubmissionTable = () => {
        return (<span>
            <SubmissionTable
                accounts={this.formatAccountDataForSubmissionTable()}
                categoryTemplates={this.props.categoryTemplates.categories}
                statuses={this.props.submissionStatuses}
            />
        </span>);
    };

    formatAccountDataForSubmissionTable = () => {
        var accounts = {};
        this.props.accounts.forEach(accountId => {
            accounts[accountId] = this.props.accountsData[accountId]
        });
        return accounts;
    };

    isSubmitButtonDisabled = () => {
        return this.props.submissionStatuses.inProgress;
    };

    areAllListingsSuccessful = () => {
        let accounts = this.props.submissionStatuses.accounts;
        if (Object.keys(accounts).length === 0) {
            return false;
        }

        let hasStatusForAccountsAndCategories = false;
        for (let accountId in accounts) {
            let account = accounts[accountId];
            for (let categoryId in account) {
                let category = account[categoryId];
                if (category.status !== "completed") {
                    return false;
                }
                hasStatusForAccountsAndCategories = true;
            }
        }

        return hasStatusForAccountsAndCategories;
    };

    areCategoryTemplatesFetching = () => {
        return this.props.categoryTemplates.isFetching;
    };

    validateProductAssignation = (event) => {
        if (this.isPbseRequired() && !this.areAllVariationsAssigned()) {
            event.preventDefault();
            this.addVariationErrorOnProductSearch();
            return;
        }

        if (this.props.productSearch.error) {
            this.props.clearErrorFromProductSearch();
        }
    };

    areAllVariationsAssigned = () => {
        return this.props.variationsDataForProduct.every(variation => {
            return !!(this.props.productSearch.selectedProducts[variation.id]);
        });
    };

    isPbseRequired = () => {
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
    };

    addVariationErrorOnProductSearch = () => {
        this.props.addErrorOnProductSearch('You must assign a product to all your variations. This must be done because one of your selected eBay categories requires all the variations of your product to be mapped to existing products.');
    };

    buildSections = () => {
        const productSearchComponent = this.renderProductSearchComponent();

        const sections = [
            new SectionData('Listing Information', this.renderForm()),
            new SectionData('Listing creation status', this.renderSubmissionTable())
        ];

        if (productSearchComponent) {
            sections.unshift(this.buildProductSearchSectionData(productSearchComponent));
        }

        return sections;
    };

    buildProductSearchSectionData = (productSearchComponent) => {
        return new SectionData(
            'Search for your product',
            productSearchComponent,
            this.validateProductAssignation,
            this.isYesButtonDisabledForProductSearch()
        );
    };

    isYesButtonDisabledForProductSearch = () => {
        return this.props.categoryTemplates.isFetching;
    };

    submitForm = () => {
        if (this.isPbseRequired() && !this.areAllVariationsAssigned()) {
            this.addVariationErrorOnProductSearch();
            $('html, body').animate({
                scrollTop: ($("a[name=section0]").offset().top)
            }, 200);
            return;
        }

        this.props.submitForm();
    };

    getYesButtonText = () => {
        if (this.isSubmitButtonDisabled() || this.areCategoryTemplatesFetching()) {
            return 'Submitting...';
        }

        if (this.areAllListingsSuccessful()) {
            return 'All done';
        }

        return 'Submit';
    };

    render() {
        return <SectionedContainer
            sectionClassName={"editor-popup product-create-listing"}
            yesButtonText={this.getYesButtonText()}
            noButtonText="Cancel"
            onYesButtonPressed={this.submitForm}
            onNoButtonPressed={this.props.onCreateListingClose}
            onBackButtonPressed={this.props.onBackButtonPressed.bind(this, this.props.product)}
            yesButtonDisabled={(this.isSubmitButtonDisabled() || this.areAllListingsSuccessful() || this.areCategoryTemplatesFetching())}
            sections={this.buildSections()}
        />;
    }
}

CreateListingPopup = reduxForm({
    form: "createListing",
    enableReinitialize: true,
    // This is required to make the images in the variation table show correctly
    keepDirtyOnReinitialize: true,
    onSubmit: submitForm,
})(CreateListingPopup);

const mapStateToProps = function(state) {
    return {
        initialValues: state.initialValues,
        initialDimensions: state.initialValues.dimensions ? Object.assign(state.initialValues.dimensions) : {},
        initialProductPrices: state.initialValues.prices ? Object.assign(state.initialValues.prices) : {},
        submissionStatuses: JSON.parse(JSON.stringify(state.submissionStatuses)),
        resetSection: resetSection,
        categoryTemplates: state.categoryTemplates,
        productSearch: state.productSearch,
        variationImages: FormSelector(state, 'images')
    };
};

const mapDispatchToProps = function(dispatch, props) {
    return {
        submitForm: function() {
            dispatch(submit("createListing"));
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
        clearSelectedProduct: function(productId) {
            dispatch(Actions.clearSelectedProduct(productId, props.variationsDataForProduct));
        },
        addErrorOnProductSearch: function(errorMessage) {
            dispatch(Actions.addErrorOnProductSearch(errorMessage));
        },
        clearErrorFromProductSearch: function() {
            dispatch(Actions.clearErrorFromProductSearch());
        }
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(CreateListingPopup);