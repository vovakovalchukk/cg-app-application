define([
    'react',
    'redux-form',
    'react-redux',
    'Common/Components/Button',
    'CategoryMapper/Actions/Category',
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
                accounts: {}
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
                            accountId={accountId}
                            categories={accountData.categories}
                            refreshing={accountData.refreshing}
                            refreshable={accountData.refreshable}
                            resetSelection={accountData.resetSelection}
                            onCategorySelected={this.props.onCategorySelected}
                            onRefreshClick={this.props.onRefreshClick}
                            onRemoveButtonClick={this.props.onRemoveButtonClick}
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
                <form onSubmit={this.props.handleSubmit}>
                    <div className={"order-form half product-container category-map-container"}>
                        <div>
                            <label>
                                <div className={"order-inputbox-holder"}>
                                    <Field name={"templateName[" + this.props.index + "]"} component="input" type="text" placeholder="Category template name here..."/>
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

    var mapDispatchToProps = function (dispatch, ownProps) {
        return {
            onCategorySelected: function(accountId, categoryId, categoryLevel) {
                dispatch(Actions.categorySelected(dispatch, ownProps.index, accountId, categoryId, categoryLevel));
            },
            onRefreshClick: function(accountId) {
                dispatch(Actions.refreshButtonClicked(dispatch, accountId));
            },
            onRemoveButtonClick: function (accountId) {
                dispatch(Actions.removeButtonClicked(ownProps.index, accountId));
            }
        };
    };

    var CategoryMapConnector = ReactRedux.connect(null, mapDispatchToProps);

    CategoryMapComponent = CategoryMapConnector(CategoryMapComponent);

    var categoryMapFormCreator = ReduxForm.reduxForm({
        form: "categoryMap",
        onSubmit: function(values, f, state) {
            console.log(values, state);
        }
    });

    return categoryMapFormCreator(CategoryMapComponent);
});
