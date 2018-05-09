define([
    'react'
], function (
    React
) {
    "use strict";

    var SubmissionTableComponent = React.createClass({
        getDefaultProps: function () {
            return {
                accounts: {},
                categoryTemplates: {}
            };
        },
        renderTableHeader: function () {
            return <tr>
                <th>Channel</th>
                <th>Account</th>
                <th>Category</th>
                <th>Status</th>
                <th>Response</th>
            </tr>;
        },
        renderStatusRows: function () {
            var statusRows = [];
            for (var accountId in this.props.accounts) {
                var account = this.props.accounts[accountId];
                for (var templateId in this.props.categoryTemplates) {
                    var categoryTemplate = this.props.categoryTemplates[templateId];
                    var category = this.findCategoryForAccountInTemplate(categoryTemplate, accountId);
                    statusRows.push(<tr>
                        <td>{account.channel}</td>
                        <td>{account.displayName}</td>
                        <td>{category.title}</td>
                        <td>{"Status - Test"}</td>
                        <td>{null}</td>
                    </tr>);
                }
            }
            return statusRows;
        },
        findCategoryForAccountInTemplate: function(template, accountId) {
            for (var categoryId in template.categories) {
                var category = template.categories[categoryId];
                if (category.accountId == accountId) {
                    return category;
                }
            }
        },
        render: function () {
            return (
                <div className={"variation-picker"}>
                    <table>
                        <thead>
                            {this.renderTableHeader()}
                        </thead>
                        {this.renderStatusRows()}
                    </table>
                </div>
            );
        }
    });

    return SubmissionTableComponent;
});