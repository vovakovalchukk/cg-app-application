import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import Actions from "../Actions/Actions";
import TypeColumn from "./Column/Type";
import InputColumn from "./Column/Input";
import FtpTestColumn from "./Column/FtpTest";

class FtpAccountsComponent extends React.Component {
    static defaultProps = {
        accounts: [],
        initialAccounts: [],
        accountTypeOptions: {},
        defaultPorts: {}
    };

    renderTableHeader = () => {
        return <tr>
            <th>Type</th>
            <th>Username</th>
            <th>Password</th>
            <th>Server</th>
            <th>Port</th>
            <th>Initial directory</th>
            <th>Test</th>
        </tr>;
    };

    renderAccountRows = () => {
        return this.props.accounts.map((account, index) => {
            return <tr>
                <td>{this.renderTypeColumn(account, index)}</td>
                <td>{this.renderTextInputColumn(account, index, 'username')}</td>
                <td>{this.renderInputColumnForType(account, index, 'password', 'password')}</td>
                <td>{this.renderTextInputColumn(account, index, 'server')}</td>
                <td>{this.renderInputColumnForType(account, index, 'port', 'number')}</td>
                <td>{this.renderTextInputColumn(account, index, 'initialDir')}</td>
                <td>{this.renderFtpTestColumn(account, index)}</td>
            </tr>;
        });
    };

    renderTypeColumn = (account, index) => {
        return <TypeColumn
            account={account}
            index={index}
            options={this.props.accountTypeOptions}
        />
    };

    renderFtpTestColumn = (account, index) => {
        return <FtpTestColumn
            account={account}
            index={index}
        />
    };

    renderTextInputColumn = (account, index, property) => {
        return this.renderInputColumnForType(account, index, property, 'text');
    };

    renderInputColumnForType = (account, index, property, type) => {
        return <InputColumn
            account={account}
            index={index}
            property={property}
            type={type}
            placeholder={'Type a ' + property}
            updateInputValue={this.onInputValueChange}
        />;
    };

    onInputValueChange = (index, property, newValue) => {
        if (this.isLastAccount(index)) {
            this.props.actions.addNewAccount({
                initialDir: 'public_html',
                password: '',
                port: this.props.defaultPorts.ftp,
                server: '',
                type: 'ftp',
                username: ''
            });
        }

        this.props.actions.updateInputValue(index, property, newValue);
    };

    isLastAccount = (index) => {
        return this.props.accounts.length - 1 === index;
    };

    render() {
        return <div>
            <form name={'ftpAccount'}>
                <table>
                    <thead>
                        {this.renderTableHeader()}
                    </thead>
                    <tbody>
                        {this.renderAccountRows()}
                    </tbody>
                </table>
            </form>
        </div>;
    }
}

const mapStateToProps = function(state) {
    return {
        accounts: state.accounts,
        initialAccounts: state.initialAccounts
    }
};

const mapDispatchToProps = function(dispatch) {
    return {
        actions: bindActionCreators(
            Actions,
            dispatch
        )
    };
};

const FtpAccountsConnector = connect(mapStateToProps, mapDispatchToProps);

export default FtpAccountsConnector(FtpAccountsComponent);
