define([
    'react',
    'react-dom',
    'react-redux',
    'redux-form',
    'Common/Components/Container',
    'Common/Components/ChannelBadge',
    'CategoryMapper/Components/CategoryMap',
    'CategoryMapper/Service/SubmitCategoryMapForm',
    'Product/Components/CreateListing/Actions/Actions',
    'Product/Components/CreateListing/Components/AccountSelect',
    'Product/Components/CreateListing/Components/CategoryMapSelect',
    'Product/Components/CreateListing/Components/SiteSelect',
    'Product/Components/CreateListing/Service/AccountSelectionFormValidator'
], function(
    React,
    ReactDom,
    ReactRedux,
    ReduxForm,
    Container,
    ChannelBadgeComponent,
    CategoryMap,
    submitCategoryMapForm,
    Actions,
    AccountSelectComponent,
    CategoryMapSelectComponent,
    SiteSelectComponent,
    accountSelectionFormValidator
) {
    "use strict";

    var Field = ReduxForm.Field;
    var FieldArray = ReduxForm.FieldArray;

    var AccountSelectionPopup = React.createClass({
        getDefaultProps: function() {
            return {
                product: {},
                addNewCategoryVisible: false
            }
        },
        componentDidMount: function() {
            this.props.fetchCategoryRoots();
        },
        renderSiteSelectField: function() {
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
        },
        renderCategorySelectField: function() {
            return <Field
                name="categories"
                component={CategoryMapSelectComponent}
                options={this.props.categoryTemplateOptions}
                onAddNewCategoryClick={this.props.showAddNewCategoryMapComponent}
                addNewCategoryMapButtonVisible={true}
                onCategorySelected={this.props.categoryMapSelected}
            />
        },
        renderAccountSelectField: function() {
            return <FieldArray
                name="accounts"
                component={AccountSelectComponent}
                accounts={this.props.accounts}
                accountSettings={this.props.accountSettings}
                fetchSettingsForAccount={this.props.fetchSettingsForAccount}
                touch={this.props.touch}
            />
        },
        renderAddNewCategoryComponent: function() {
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
        },
        onViewExistingMapClick: function(categoryName) {
            this.props.categoryMapSelectedByName(categoryName);
        },
        renderForm: function() {
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
        },
        render: function() {
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
                >
                    {this.renderForm()}
                </Container>
            );
        }
    });

    AccountSelectionPopup = ReduxForm.reduxForm({
        form: "accountSelection",
        initialValues: {
            accounts: [],
            categories: []
        },
        onSubmit: function(values, dispatch, props) {
            var accounts = [];
            values.accounts.forEach(function (accountId) {
                if (accountId) {
                    accounts.push(accountId);
                }
            });

            values = Object.assign(values, {
                product: props.product,
                accounts: values.accounts.filter(accountId => accountId)
            });
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

    var convertStateToCategoryMaps = function (state) {
        var categories = {},
            accountId;

        for (accountId in state.accounts) {
            categories[accountId] = Object.assign({}, state.accounts[accountId], {
                categories: Object.assign({}, state.categories[accountId]),
                selectedCategories: getSelectedCategoriesFromState(state, accountId),
                displayName: state.accounts[accountId].name
            });
        }

        return categories;
    };

    var mapStateToProps = function (state) {
        return {
            accounts: Object.assign({}, state.accounts),
            addNewCategoryVisible: state.addNewCategoryVisible.isVisible,
            categoryTemplateOptions: Object.assign({}, state.categoryTemplateOptions),
            accountsForCategoryMap: convertStateToCategoryMaps(state),
            accountSettings: state.accountSettings
        }
    };

    var mapDispatchToProps = function (dispatch) {
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
                dispatch(ReduxForm.submit("accountSelection"));
            },
            fetchSettingsForAccount: function(accountId) {
                dispatch(Actions.fetchSettingsForAccount(accountId, dispatch));
            }
        }
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(AccountSelectionPopup);
});
