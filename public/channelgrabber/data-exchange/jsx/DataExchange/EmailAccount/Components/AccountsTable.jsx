import React from 'react';

const TYPE_FROM = 'from';
const TYPE_TO = 'to';

class EmailAccountsTable extends React.Component {
    static defaultProps = {
        accounts: [],
        type: TYPE_TO
    };

    isTypeFrom() {
        return this.props.type == TYPE_FROM;
    }

    renderTableHeader() {
        return <tr>
            <th colSpan={this.isTypeFrom() ? "3" : "2"}>
                {"Send " + this.props.type + " Email Address"}
            </th>
        </tr>;
    }

    renderAccountRows() {
        const isTypeFrom = this.isTypeFrom();

        let accounts = this.props.accounts.map(account => {
            return <tr>
                <td>{account.address}</td>
                {isTypeFrom ? this.renderVerifyColumn(account) : null}
                <td>{this.renderRemoveColumn(account)}</td>
            </tr>;
        });

        accounts.push(this.addEmptyTableRow());

        return accounts;
    }

    renderVerifyColumn(account) {
        return <td>"Verify " + {account.id}</td>;
    }

    addEmptyTableRow() {
        return <tr>
            <td>empty</td>
            {this.isTypeFrom() ? <td></td> : null}
            <td></td>
        </tr>;
    }

    renderRemoveColumn(account) {
        return <span className="remove-icon">
            <i
                className='fa fa-2x fa-minus-square icon-create-listing'
                aria-hidden='true'
            />
        </span>;
    }

    render() {
        return (
            <div className={"email-accounts"}>
                <table>
                    <thead>
                        {this.renderTableHeader()}
                    </thead>
                    <tbody>
                        {this.renderAccountRows()}
                    </tbody>
                </table>
            </div>
        );
    }
}

export default EmailAccountsTable;
export {TYPE_FROM as EmailAccountTypeFrom, TYPE_TO as EmailAccountTypeTo};
