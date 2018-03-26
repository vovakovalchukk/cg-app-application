define([
    'react',
    'redux-form',
    'Common/Components/Button',
    'CategoryMapper/Components/AccountCategorySelect'
], function(
    React,
    ReduxForm,
    Button,
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
                        <div className={"order-inputbox-holder"}>
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
                        </div>
                    </label>
                );
            };
            return <span>{selects}</span>;
        },
        render: function() {
            return (
                <form onSubmit={this.props.handleSubmit}>
                    <div className={"order-form half"}>
                        <label>
                            <div className={"order-inputbox-holder"}>
                                <Field name="templateName" component="input" type="text" placeholder="Category template name here..."/>
                            </div>
                        </label>
                        {this.renderCategorySelects()}
                        <div style={{width: 80, display: 'flex'}}>
                            <Button text={"Save"} onClick={this.props.handleSubmit}/>
                        </div>
                    </div>
                </form>
            );
        }
    });

    return CategoryMapComponent;
});
