define([
    'react',
    'redux-form',
    'react-redux',
    'Common/Components/Button',
    'Common/Components/EditableField',
    'CategoryMapper/Actions/Actions',
    'CategoryMapper/Components/AccountCategorySelect'
], function(
    React,
    ReduxForm,
    ReactRedux,
    Button,
    EditableField,
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
                                        type="text" placeholder="Category template name here..."
                                    />
                                </div>
                            </label>
                            <label className={"save-button"}>
                                <div className={"button container-btn yes"} onClick={this.props.handleSubmit}>
                                    <span>Save</span>
                                </div>
                            </label>
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

    var CategoryMapConnector = ReactRedux.connect(
        function (state, ownProps) {
            var initialValues = {},
                values = {};

            if (ownProps.mapId in state.initialValues) {
                initialValues = formatFormData(state.initialValues[ownProps.mapId]);
            }

            return {
                form: 'categoryMap-' + ownProps.mapId,
                initialValues: initialValues
            }
        },
        null
    );

    return CategoryMapConnector(CategoryMapComponent);
});
