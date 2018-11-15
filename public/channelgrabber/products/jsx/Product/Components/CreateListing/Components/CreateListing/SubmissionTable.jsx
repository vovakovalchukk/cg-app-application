import React from 'react';
import PopupMessage from 'Common/Components/Popup/Message';


const DEFAULT_STATUS = "Not started";
const STATUS_STARTED = "started";

class SubmissionTableComponent extends React.Component {
    static defaultProps = {
        accounts: {},
        categoryTemplates: {},
        statuses: {}
    };

    state = {
        showErrors: null
    };

    renderTableHeader = () => {
        return <tr>
            <th>Channel</th>
            <th>Account</th>
            <th>Category</th>
            <th>Status</th>
            <th>Response</th>
        </tr>;
    };

    renderStatusRows = () => {
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
    };

    findCategoryForAccountInTemplate = (template, accountId) => {
        var category = template.accounts[accountId];
        return Object.assign(category, {
            id: category.categoryId
        });
    };

    getStatusForAccountAndCategory = (accountId, categoryId) => {
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
    };

    getDefaultStatus = () => {
        return this.props.statuses.inProgress ? STATUS_STARTED : DEFAULT_STATUS;
    };

    getResponseForAccountAndCategory = (accountId, categoryId) => {
        var status = this.getStatusForAccountAndCategory(accountId, categoryId);
        if (status == "error") {
            return <span className="view-errors-button" onClick={this.onShowErrorsClick.bind(this, accountId, categoryId)}>
                Click here to show errors
            </span>;
        } else if (status == "completed") {
            return "Successful";
        }

        return null;
    };

    onShowErrorsClick = (accountId, categoryId) => {
        this.setState({
            showErrors: {
                accountId: accountId,
                categoryId: categoryId
            }
        });
    };

    renderErrorMessage = () => {
        if (!this.state.showErrors) {
            return null;
        }
        let errors = this.findDataForAccountAndCategory(this.state.showErrors.accountId, this.state.showErrors.categoryId, 'errors');
        let warnings = this.findDataForAccountAndCategory(this.state.showErrors.accountId, this.state.showErrors.categoryId, 'warnings');

        if (errors.length === 0 && warnings.length === 0) {
            return null;
        }

        return (
            <PopupMessage
                initiallyActive={true}
                headerText="There were errors when trying to create the listing"
                className="error"
                onCloseButtonPressed={this.onErrorMessageClosed}
            >
                {this.renderSection(errors, 'Errors')}
                {this.renderSection(warnings, 'Warnings')}
                <p>Please address these errors then try again.</p>
            </PopupMessage>
        );
    };

    renderSection = (data, title)  => {
        if (data.length === 0) {
            return null;
        }

        return <span>
            <h4>{title}</h4>
            <ul>
                {data.map(function (error) {
                    return (<li>{error}</li>);
                })}
            </ul>
        </span>;
    };

    findDataForAccountAndCategory = (accountId, categoryId, type) => {
        let accounts = this.props.statuses.accounts;

        if (!accounts[accountId] || !accounts[accountId][categoryId]) {
            return [];
        }

        let dataForAccountAndCategory = accounts[accountId][categoryId];
        return dataForAccountAndCategory[type] ? dataForAccountAndCategory[type] : [];
    };

    onErrorMessageClosed = () => {
        this.setState({
            showErrors: null
        });
    };

    render() {
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
}

export default SubmissionTableComponent;
