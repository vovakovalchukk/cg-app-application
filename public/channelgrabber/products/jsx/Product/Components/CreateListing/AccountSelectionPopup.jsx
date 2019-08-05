import React from 'react';
import {connect} from 'react-redux';
import {Field, FieldArray, reduxForm, SubmissionError, submit as reduxFormSubmit} from 'redux-form';
import Container from 'Common/Components/Container';
import CategoryMap from 'CategoryMapper/Components/CategoryMap';
import submitCategoryMapForm from 'CategoryMapper/Service/SubmitCategoryMapForm';
import BlockerModal from 'Common/Components/BlockerModal';
import Actions from 'Product/Components/CreateListing/Actions/Actions';
import AccountSelectComponent from 'Product/Components/CreateListing/Components/AccountSelect';
import CategoryMapSelectComponent from 'Product/Components/CreateListing/Components/CategoryMapSelect';
import SiteSelectComponent from 'Product/Components/CreateListing/Components/SiteSelect';
import accountSelectionFormValidator from 'Product/Components/CreateListing/Service/AccountSelectionFormValidator';

class AccountSelectionPopup extends React.Component {
    static defaultProps = {
        product: {},
        addNewCategoryVisible: false,
        listingCreationAllowed: null,
        managePackageUrl: null,
        salesPhoneNumber: null,
        demoLink: null,
        productSearchActive: false,
        productSearchActiveForVariations: false,
        renderCreateListingPopup: () => {},
        renderSearchPopup: () => {}
    };

    componentDidMount() {
        this.props.fetchCategoryRoots();
    }

    renderSiteSelectField = () => {
        for (var accountId in this.props.accounts) {
            var account = this.props.accounts[accountId];
            if (account.channel == 'ebay') {
                return <Field
                    name="site"
                    component={SiteSelectComponent}
                    options={this.props.ebaySiteOptions}
                />
            }
        }
        return null;
    };

    renderCategorySelectField = () => {
        return <Field
            name="categories"
            component={CategoryMapSelectComponent}
            options={this.props.categoryTemplateOptions}
            onAddNewCategoryClick={this.props.showAddNewCategoryMapComponent}
            addNewCategoryMapButtonVisible={true}
            onCategorySelected={this.props.categoryMapSelected}
        />
    };

    renderAccountSelectField = () => {
        return <FieldArray
            name="accounts"
            component={AccountSelectComponent}
            accounts={this.props.accounts}
            accountSettings={this.props.accountSettings}
            fetchSettingsForAccount={this.props.fetchSettingsForAccount}
            touch={this.props.touch}
        />
    };

    renderBlockerModal = () => {
        return <BlockerModal
            headerText={'Access Listings Now'}
            contentJsx={
                <span>
                    <p>Create multiple listings in one go from one simple interface. </p>
                    <p>Generate more sales with more listings. </p>
                </span>
            }
            buttonText={'Add Listings To My Subscription'}
            buttonOnClick={() => {
                window.location = 'https://' + this.props.managePackageUrl;
            }}
            footerJsx={
                <span>
                    Not sure? Contact our ecommerce specialists on {this.props.salesPhoneNumber} to discuss or&nbsp;
                    <a href={this.props.demoLink}
                       alt="calendar-diary"
                       target="_blank"
                    >
                        Click Here
                    </a>
                    &nbsp;to book a demo.
                </span>
            }
        />
    };

    renderAddNewCategoryComponent = () => {
        if (!this.props.addNewCategoryVisible) {
            return;
        }
        return (<span className="form-input-container category-template-container">
            <CategoryMap
                accounts={this.props.accountsForCategoryMap}
                mapId={0}
                onSubmit={submitCategoryMapForm}
                onViewExistingMapClick={this.onViewExistingMapClick}
                closeButtonVisible={true}
                onCloseButtonPressed={this.props.hideAddNewCategoryMapComponent}
            />
        </span>);
    };

    onViewExistingMapClick = (categoryName) => {
        this.props.categoryMapSelectedByName(categoryName);
    };

    renderForm = () => {
        return (
            <form
                onSubmit={this.props.handleSubmit}
                className="account-select-form"
            >
                {this.renderAccountSelectField()}
                {this.renderSiteSelectField()}
                {this.renderCategorySelectField()}
                {this.renderAddNewCategoryComponent()}
            </form>
        );
    };

    isSubmitButtonDisabled = () => {
        if (this.props.invalid) {
            return true;
        }
        for (var accountId in this.props.accounts) {
            var account = this.props.accounts[accountId];
            if (account.isFetching) {
                return true;
            }
        }
        return false;
    };

    isAccountSelectDisabled = () => {
        for (var accountId in this.props.accounts) {
            var account = this.props.accounts[accountId];
            if (account.isFetching) {
                return true;
            }
        }
        return false;
    };

    render() {
        return (
            <Container
                initiallyActive={true}
                className="editor-popup product-create-listing account-form"
                closeOnYes={false}
                headerText={"Selects accounts to list to"}
                onNoButtonPressed={this.props.onCreateListingClose}
                onYesButtonPressed={this.props.submitForm}
                yesButtonText="Next"
                noButtonText="Cancel"
                yesButtonDisabled={this.isSubmitButtonDisabled()}
            >
                {!this.props.listingCreationAllowed ? this.renderBlockerModal() : ''}
                {this.renderForm()}
            </Container>
        );
    }
}

var filterOutEmptyAccountSettingsData = function(accountSettings) {
    var accountDefaultSettings = {};
    for (var accountId in accountSettings) {
        if (accountSettings[accountId].settings) {
            accountDefaultSettings[accountId] = accountSettings[accountId].settings;
        }
    }
    return accountDefaultSettings;
};

const getSearchAccountId = function(props) {
    let accounts = props.accounts;

    let accountIndex = Object.keys(accounts).findIndex(accountId=> {
        let accountData =  accounts[accountId];

        if (!accountData) {
            return false;
        }

        if (accountData.channel !== 'ebay' || !accountData.listingsAuthActive || accountData.active == false) {
            return false;
        }

        return true;
    });

    return accountIndex > -1 ? Object.keys(accounts)[accountIndex] : null;
};

const isProductSearchActive = function(props) {
    return props.productSearchActive && (props.product.variationCount > 1 ? props.productSearchActiveForVariations : true);
};

AccountSelectionPopup = reduxForm({
    form: "accountSelection",
    initialValues: {
        accounts: [],
        categories: []
    },
    onSubmit: function(values, dispatch, props) {
        var errors = accountSelectionFormValidator(values, props);
        if (errors && Object.keys(errors).length > 0) {
            throw new SubmissionError(errors);
        }

        var accounts = [];
        values.accounts.forEach(function(accountId) {
            if (accountId) {
                accounts.push(accountId);
            }
        });

        values = Object.assign(values, {
            product: props.product,
            accounts: values.accounts.filter(accountId => accountId),
            accountDefaultSettings: filterOutEmptyAccountSettingsData(props.accountSettings),
            productSearchActive: isProductSearchActive(props)
        });

        values.searchAccountId = getSearchAccountId(values);

        props.renderCreateListingPopup(values);
    },
    validate: accountSelectionFormValidator
})(AccountSelectionPopup);

var getSelectedCategoriesFromState = function(state, accountId) {
    if (0 in state.categoryMaps) {
        var selectedCategories = state.categoryMaps[0].selectedCategories;
        if (accountId in selectedCategories) {
            return selectedCategories[accountId];
        }
    }
    return [];
};

var isRefreshableChannel = function(channel) {
    const channelsThatAreNotRefreshable = [
        'ebay',
        'amazon'
    ];
    return channelsThatAreNotRefreshable.indexOf(channel) === -1;
};

var convertStateToCategoryMaps = function(state) {
    var categories = {},
        accountId;

    for (accountId in state.accounts) {
        categories[accountId] = Object.assign({}, state.accounts[accountId], {
            categories: Object.assign({}, state.categories[accountId]),
            selectedCategories: getSelectedCategoriesFromState(state, accountId),
            displayName: state.accounts[accountId].name
        });
    }

    for (let category in categories) {
        categories[category].refreshable = isRefreshableChannel(categories[category].channel);
    }

    return categories;
};

var mapStateToProps = function(state) {
    return {
        accounts: Object.assign({}, state.accounts),
        addNewCategoryVisible: state.addNewCategoryVisible.isVisible,
        categoryTemplateOptions: Object.assign({}, state.categoryTemplateOptions),
        accountsForCategoryMap: convertStateToCategoryMaps(state),
        accountSettings: state.accountSettings
    }
};

var mapDispatchToProps = function(dispatch) {
    return {
        fetchCategoryRoots: function() {
            dispatch(Actions.fetchCategoryRoots(dispatch));
        },
        showAddNewCategoryMapComponent: function() {
            dispatch(Actions.showAddNewCategoryMapComponent());
        },
        hideAddNewCategoryMapComponent: function() {
            dispatch(Actions.hideAddNewCategoryMapComponent());
        },
        categoryMapSelected: function(categoryIds) {
            dispatch(Actions.categoryMapSelected(categoryIds));
        },
        categoryMapSelectedByName: function(name) {
            dispatch(Actions.categoryMapSelectedByName(name));
        },
        submitForm: function() {
            dispatch(reduxFormSubmit("accountSelection"));
        },
        fetchSettingsForAccount: function(accountId) {
            dispatch(Actions.fetchSettingsForAccount(accountId, dispatch));
        }
    }
};

export default connect(mapStateToProps, mapDispatchToProps)(AccountSelectionPopup);

