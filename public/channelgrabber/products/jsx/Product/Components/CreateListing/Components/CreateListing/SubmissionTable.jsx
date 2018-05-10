define([
    'react'
], function (
    React
) {
    "use strict";

    const defaultStatus = "Not started";

    var SubmissionTableComponent = React.createClass({
        getDefaultProps: function () {
            return {
                accounts: {},
                categoryTemplates: {},
                statuses: {}
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
                        <td>{this.getStatusForAccountAndCategory(accountId, category.id)}</td>
                        <td>{this.getResponseForAccountAndCategory(accountId, category.id)}</td>
                    </tr>);
                }
            }
            return statusRows;
        },
        findCategoryForAccountInTemplate: function(template, accountId) {
            for (var categoryId in template.categories) {
                var category = template.categories[categoryId];
                if (category.accountId == accountId) {
                    return Object.assign(category, {
                        id: categoryId
                    });
                }
            }
        },
        getStatusForAccountAndCategory: function (accountId, categoryId) {
            if (!this.props.statuses.accounts || Object.keys(this.props.statuses.accounts).length === 0) {
                return defaultStatus;
            }

            var accounts = this.props.statuses.accounts;
            if (!accounts[accountId]) {
                return defaultStatus;
            }

            var account = accounts[accountId];
            if (!account[categoryId]) {
                return defaultStatus;
            }

            var category = account[categoryId];
            return category.status ? category.status : defaultStatus;
        },
        getResponseForAccountAndCategory: function (accountId, categoryId) {
            if (!this.props.statuses.accounts || Object.keys(this.props.statuses.accounts).length === 0) {
                return null;
            }

            var accounts = this.props.statuses.accounts;
            if (!accounts[accountId]) {
                return null;
            }

            var account = accounts[accountId];
            if (!account[categoryId]) {
                return null;
            }

            var category = account[categoryId];
            if (category.errors.length > 0) {
                return category.errors.join(", ");
            }

            return null;
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