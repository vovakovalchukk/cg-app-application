import React from 'react';
import ReactDom from 'react-dom';
import {connect} from 'react-redux';
import {Field, FieldArray, FormSection, reduxForm, resetSection, submit as reduxFormSubmit} from 'redux-form';
import Container from 'Common/Components/Container';
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
        selectedProductDetails: {}
    };

    componentDidMount() {
        this.props.loadInitialValues();
    }

    componentWillUnmount() {
        this.props.resetSubmissionStatuses();
    }

    renderForm = () => {
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
            categoryTemplates={this.props.categoryTemplates}
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
            categoryTemplates={this.props.categoryTemplates}
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
            />
        </span>);
    };

    getSelectedAccountsData = () => {
        var accounts = [];
        this.props.accounts.map(function(accountId) {
            accounts.push(this.props.accountsData[accountId]);
        }.bind(this));
        return accounts;
    };

    renderSubmissionTable = () => {
        return (<span>
            <span className="heading-large heading-table">Creation status</span>
            <SubmissionTable
                accounts={this.formatAccountDataForSubmissionTable()}
                categoryTemplates={this.props.categoryTemplates}
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

    render() {
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
}

CreateListingPopup = reduxForm({
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
        submissionStatuses: JSON.parse(JSON.stringify(state.submissionStatuses)),
        resetSection: resetSection
    };
};

var mapDispatchToProps = function(dispatch, props) {
    return {
        submitForm: function() {
            dispatch(reduxFormSubmit("createListing"));
        },
        loadInitialValues: function() {
            dispatch(
                Actions.loadInitialValues(
                    props.product,
                    props.variationsDataForProduct,
                    props.accounts,
                    props.accountDefaultSettings,
                    props.accountsData,
                    props.categoryTemplates,
                    props.selectedProductDetails ? props.selectedProductDetails : {}
                )
            );
        },
        resetSubmissionStatuses: function () {
            dispatch(Actions.resetSubmissionStatuses());
        }
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(CreateListingPopup);

