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
    'Product/Components/CreateListing/Components/SiteSelect'
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
    SiteSelectComponent
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
            />
        },
        renderAccountSelectField: function() {
            return <FieldArray
                name="accounts"
                component={AccountSelectComponent}
                accounts={this.props.accounts}
            />
        },
        renderAddNewCategoryComponent: function() {
            if (!this.props.addNewCategoryVisible) {
                return;
            }
            return <CategoryMap
                accounts={this.props.accountsForCategoryMap}
                mapId={0}
                onSubmit={submitCategoryMapForm}
            />;
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
                    className="editor-popup product-create-listing"
                    closeOnYes={false}
                    headerText={"Selects accounts to list to"}
                    onNoButtonPressed={this.props.onCreateListingClose}
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
        onSubmit: function() {
            console.log(arguments);
        },
        onChange: function(values) {
            console.log(values);
        }
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
        }
    };

    var mapDispatchToProps = function (dispatch) {
        return {
            fetchCategoryRoots: function() {
                dispatch(Actions.fetchCategoryRoots(dispatch));
            },
            showAddNewCategoryMapComponent: function() {
                dispatch(Actions.showAddNewCategoryMapComponent());
            }
        }
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(AccountSelectionPopup);
});
