import React from 'react';
import PopupMessage from 'Common/Components/Popup/Message';
    

    const DEFAULT_STATUS = "Not started";
    const STATUS_STARTED = "started";

    var SubmissionTableComponent = React.createClass({
        getDefaultProps: function () {
            return {
                accounts: {},
                categoryTemplates: {},
                statuses: {}
            };
        },
        getInitialState: function () {
            return {
                showErrors: null
            }
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
            var category = template.accounts[accountId];
            return Object.assign(category, {
                id: category.categoryId
            });
        },
        getStatusForAccountAndCategory: function (accountId, categoryId) {
            if (!this.props.statuses.accounts || Object.keys(this.props.statuses.accounts).length === 0) {
                return this.getDefaultStatus();
            }

            var accounts = this.props.statuses.accounts;
            if (!accounts[accountId]) {
                return this.getDefaultStatus();
            }

            var account = accounts[accountId];
            if (!account[categoryId]) {
                return this.getDefaultStatus();
            }

            var category = account[categoryId];
            return category.status ? category.status : this.getDefaultStatus();
        },
        getDefaultStatus: function() {
            return this.props.statuses.inProgress ? STATUS_STARTED : DEFAULT_STATUS;
        },
        getResponseForAccountAndCategory: function (accountId, categoryId) {
            var status = this.getStatusForAccountAndCategory(accountId, categoryId);
            if (status == "error") {
                return <span className="view-errors-button" onClick={this.onShowErrorsClick.bind(this, accountId, categoryId)}>
                    Click here to show errors
                </span>;
            } else if (status == "completed") {
                return "Successful";
            }

            return null;
        },
        onShowErrorsClick: function (accountId, categoryId) {
            this.setState({
                showErrors: {
                    accountId: accountId,
                    categoryId: categoryId
                }
            });
        },
        renderErrorMessage: function() {
            if (!this.state.showErrors) {
                return null;
            }
            return (
                <PopupMessage
                    initiallyActive={true}
                    headerText="There were errors when trying to create the listing"
                    className="error"
                    onCloseButtonPressed={this.onErrorMessageClosed}
                >
                    <h4>Errors</h4>
                    <ul>
                        {this.findErrorsForAccountAndCategory(this.state.showErrors.accountId, this.state.showErrors.categoryId).map(function (error) {
                            return (<li>{error}</li>);
                        })}
                    </ul>
                    <h4>Warnings</h4>
                    <ul>
                        {this.findWarningForAccountAndCategory(this.state.showErrors.accountId, this.state.showErrors.categoryId).map(function (warning) {
                            return (<li>{warning}</li>);
                        })}
                    </ul>
                    <p>Please address these errors then try again.</p>
                </PopupMessage>
            );
        },
        findErrorsForAccountAndCategory: function(accountId, categoryId) {
            return this.props.statuses.accounts[accountId][categoryId].errors;
        },
        findWarningForAccountAndCategory: function(accountId, categoryId) {
            return this.props.statuses.accounts[accountId][categoryId].warnings;
        },
        onErrorMessageClosed: function () {
            this.setState({
                showErrors: null
            });
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
                    {this.renderErrorMessage()}
                </div>
            );
        }
    });

    export default SubmissionTableComponent;
