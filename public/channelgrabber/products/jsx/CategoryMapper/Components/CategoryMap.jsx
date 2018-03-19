define([
    'react',
    'redux-form',
    'CategoryMapper/Containers/CategorySelect'
], function(
    React,
    ReduxForm,
    CategorySelect
) {
    "use strict";

    var Field = ReduxForm.Field;
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
                            <Field name="category"
                                component={CategorySelect}
                                categories={accountData}
                                accountId={accountId}
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
                    </div>
                </form>
            );
        }
    });

    return CategoryMapComponent;
});
