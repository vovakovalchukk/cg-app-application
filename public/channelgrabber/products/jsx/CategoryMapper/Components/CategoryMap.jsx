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
                mapId: null,
                name: '',
                etag: ''
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
                            name={'map[' + this.props.mapId + '].categories'}
                            accountId={accountId}
                            categories={accountData.categories}
                            refreshing={accountData.refreshing}
                            refreshable={accountData.refreshable}
                            resetSelection={accountData.resetSelection}
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
                <div className={"order-form half product-container category-map-container"}>
                    <div>
                        <label>
                            <div className={"order-inputbox-holder"}>
                                <Field
                                    name={'map[' + this.props.mapId + '].name'}
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
            );
        }
    });

    return CategoryMapComponent;
});
