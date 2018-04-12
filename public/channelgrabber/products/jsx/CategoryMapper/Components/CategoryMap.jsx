define([
    'react',
    'redux-form',
    'react-redux',
    'Common/Components/Button',
    'CategoryMapper/Actions/Actions',
    'CategoryMapper/Components/AccountCategorySelect'
], function(
    React,
    ReduxForm,
    ReactRedux,
    Button,
    Actions,
    AccountCategorySelect
) {
    "use strict";

    var Field = ReduxForm.Field;
    var FieldArray = ReduxForm.FieldArray;

    var CategoryMapComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null,
                accounts: {},
                mapId: null
            };
        },
        renderCategorySelects: function() {
            var selects = [];
            for (var accountId in this.props.accounts) {
                var accountData = this.props.accounts[accountId];
                selects.push(
                    <label>
                        <span
                            className={"inputbox-label"}>{accountData.displayName}
                        </span>
                        <FieldArray
                            component={AccountCategorySelect}
                            name={'categories'}
                            accountId={accountId}
                            categories={accountData.categories}
                            refreshing={accountData.refreshing}
                            refreshable={accountData.refreshable}
                            selectedCategories={accountData.selectedCategories ? accountData.selectedCategories : []}
                            onCategorySelected={this.props.onCategorySelected.bind(this, this.props.mapId)}
                            onRefreshClick={this.props.onRefreshClick}
                            onRemoveButtonClick={this.props.onRemoveButtonClick.bind(this, this.props.mapId)}
                        />
                    </label>
                );
            };
            return <div className={"category-selects-container"}>
                {selects}
            </div>;
        },
        render: function() {
            return (
                <form
                    onSubmit={this.props.handleSubmit}
                    name={'categoryMap-' + this.props.mapId}
                >
                    <div className={"order-form half product-container category-map-container"}>
                        <div>
                            <label>
                                <div className={"order-inputbox-holder"}>
                                    <Field
                                        name={'name'}
                                        component="input"
                                        type="text"
                                        placeholder="Category template name here..."
                                    />
                                </div>
                            </label>
                            <label className={"map-button save-button"}>
                                <div className={"button container-btn yes"} onClick={this.props.handleSubmit}>
                                    <span>Save</span>
                                </div>
                            </label>
                            {this.props.mapId > 0 &&
                                (<label className={"map-button remove-button"}>
                                    <div className={"button container-btn yes"} onClick={this.props.deleteCategoryMap.bind(this, this.props.mapId)}>
                                        <span>Delete</span>
                                    </div>
                                </label>)
                            }
                        </div>
                        {this.renderCategorySelects()}
                    </div>
                </form>
            );
        }
    });

    var categoryMapFormCreator = ReduxForm.reduxForm({
        enableReinitialize: true
    });

    CategoryMapComponent = categoryMapFormCreator(CategoryMapComponent);

    var formatFormData = function(categoryMap) {
        var data = [],
            categoriesForAccount,
            categoryId,
            categories;

        categories = [];

        for (var accountId in categoryMap.selectedCategories) {
            categoriesForAccount = categoryMap.selectedCategories[accountId];
            categoryId = categoriesForAccount[categoriesForAccount.length - 1];
            categories[accountId] = categoryId;
        }

        return {
            name: categoryMap.name,
            etag: categoryMap.etag,
            categories: categories
        }
    }

    var mapStateToProps = function (state, ownProps) {
        var initialValues = {},
            values = {};

        if (ownProps.mapId in state.initialValues) {
            initialValues = formatFormData(state.initialValues[ownProps.mapId]);
        }

        return {
            form: 'categoryMap-' + ownProps.mapId,
            initialValues: initialValues
        }
    };

    var mapDispatchToProps = function (dispatch) {
        return {
            onCategorySelected: function(mapId, accountId, categoryId, categoryLevel, selectedCategories) {
                dispatch(Actions.categorySelected(dispatch, mapId, accountId, categoryId, categoryLevel, selectedCategories));
            },
            onRefreshClick: function(accountId) {
                dispatch(Actions.refreshButtonClicked(dispatch, accountId));
            },
            onRemoveButtonClick: function (mapId, accountId) {
                dispatch(Actions.removeButtonClicked(mapId, accountId));
            },
            deleteCategoryMap: function (mapId) {
                dispatch(Actions.deleteCategoryMap(dispatch, mapId));
            }
        };
    };

    var CategoryMapConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);
    return CategoryMapConnector(CategoryMapComponent);
});
