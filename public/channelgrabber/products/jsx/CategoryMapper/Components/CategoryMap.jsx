define([
    'react',
    'redux-form',
    'CategoryMapper/Components/CategorySelect'
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
                accounts: {},
                categories: []
            };
        },
        getCategoriesOptionsForAccount: function (accountId, accountData) {
            return this.formatCategorySelectOptions(
                this.findCategoriesByAccountId(accountId)
            );
        },
        findCategoriesByAccountId: function (accountId) {
            for (var category of this.props.categories) {
                if (category.accountId == accountId) {
                    return category.categories;
                }
            }
        },
        formatCategorySelectOptions: function (categories) {
            var options = [];
            for (var categoryId in categories) {
                options.push({'name': categories[categoryId].title, 'value': categoryId});
            }
            return options;
        },
        renderCategorySelects: function() {
            var selects = [];
            for (var accountId in this.props.accounts) {
                selects.push(
                    <div className={"order-form half"}>
                        <div className={"order-inputbox-holder"}>
                            <Field name="category"
                                component={CategorySelect}
                                categories={this.getCategoriesOptionsForAccount(accountId, this.props.accounts[accountId])}
                            />
                        </div>
                    </div>
                );
            };
            return <span>{selects}</span>;
        },
        render: function() {
            return (
                <form onSubmit={this.props.handleSubmit}>
                    <div className={"order-form half"}>
                        <div className={"order-inputbox-holder"}>
                            <Field name="templateName" component="input" type="text" placeholder="HAHAH"/>
                        </div>
                    </div>
                    {this.renderCategorySelects()}
                </form>
            );
        }
    });

    return CategoryMapComponent;
});
