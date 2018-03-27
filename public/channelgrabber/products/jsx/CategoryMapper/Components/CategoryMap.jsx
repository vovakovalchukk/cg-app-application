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
                            style={{ width: 300 }}
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
            return <div style={{ display: 'inline-flex' }}>
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
                                    <Field name="templateName" component="input" type="text" placeholder="Category template name here..."/>
                                </div>
                            </label>
                            <label className={"save-button"}>
                                <div className={"button container-btn yes"} onClick={this.props.handleSubmit}>
                                    <i className="fa fa-floppy-o save-icon" aria-hidden="true"/>
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

    var mapStateToProps = function(state) {
        var categoryMap = state.categoryMap;
        return {
            accounts: categoryMap
        }
    };

    var mapDispatchToProps = function (dispatch) {
        return {
            onCategorySelected: function(accountId, categoryId, categoryLevel) {
                dispatch(Actions.categorySelected(dispatch, accountId, categoryId, categoryLevel));
            },
            onRefreshClick: function(accountId) {
                dispatch(Actions.refreshButtonClicked(dispatch, accountId));
            },
            onRemoveButtonClick: function (accountId) {
                dispatch(Actions.removeButtonClicked(accountId));
            }
        };
    };

    var CategoryMapConnector = ReactRedux.connect(mapStateToProps, mapDispatchToProps);

    CategoryMapComponent = CategoryMapConnector(CategoryMapComponent);

    var categoryMapFormCreator = ReduxForm.reduxForm({
        form: "categoryMap"
    });

    return categoryMapFormCreator(CategoryMapComponent);
});
