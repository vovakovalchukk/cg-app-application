import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import styled from "styled-components";
import Actions from "../Actions/Actions";
import TypeColumn from "./Column/Type";
import FtpTestColumn from "./Column/FtpTest";
import ActionsColumn from "../../Components/Actions";
import InputColumn from "../../Components/Input";

const Container = styled.div`
    margin-top: 45px;
`;
const TypeCell = styled.td`
    overflow: visible;
`;
const TableHeader = styled.th`
    width: ${props => props.width ? props.width : 'auto'};
`;

class FtpAccountsComponent extends React.Component {
    static defaultProps = {
        accounts: [],
        initialAccounts: [],
        accountTypeOptions: {},
        defaultPorts: {}
    };

    componentDidMount = () => {
        this.addNewFtpAccount();
    };

    isLastAccount = (index) => {
        return this.props.accounts.length - 1 === index;
    };

    getInitialAccountValueForAccount = (index) => {
        return this.props.initialAccounts[index] ? this.props.initialAccounts[index] : {};
    };

    hasAccountChanged = (index, account) => {
        let initialAccount = this.getInitialAccountValueForAccount(index);
        return !(Object.keys(account).reduce((isInitial, key) => {
            return isInitial && account[key] == initialAccount[key];
        }));
    };

    renderTableHeader = () => {
        return <tr>
            <TableHeader width={'100px'}>Type</TableHeader>
            <TableHeader>Username</TableHeader>
            <TableHeader>Password</TableHeader>
            <TableHeader>Server</TableHeader>
            <TableHeader>Port</TableHeader>
            <TableHeader>Initial directory</TableHeader>
            <TableHeader>Test</TableHeader>
            <TableHeader width={'80px'}>Actions</TableHeader>
        </tr>;
    };

    renderAccountRows = () => {
        return this.props.accounts.map((account, index) => {
            return <tr>
                <TypeCell>{this.renderTypeColumn(account, index)}</TypeCell>
                <td>{this.renderTextInputColumn(account, index, 'username')}</td>
                <td>{this.renderInputColumnForType(account, index, 'password', 'password')}</td>
                <td>{this.renderTextInputColumn(account, index, 'server')}</td>
                <td>{this.renderInputColumnForType(account, index, 'port', 'number')}</td>
                <td>{this.renderTextInputColumn(account, index, 'initialDir')}</td>
                <td>{this.renderFtpTestColumn(account, index)}</td>
                <td>{this.renderActionsColumn(account, index)}</td>
            </tr>;
        });
    };

    renderTypeColumn = (account, index) => {
        return <TypeColumn
            account={account}
            index={index}
            options={this.props.accountTypeOptions}
            updateInputValue={this.props.actions.updateInputValue}
        />
    };

    renderFtpTestColumn = (account, index) => {
        return <FtpTestColumn
            account={account}
            index={index}
            onClick={this.props.actions.testFtpAccount.bind(this, index, account)}
            hasAccountChanged={this.hasAccountChanged(index, account)}
        />
    };

    renderActionsColumn = (account, index) => {
        return <ActionsColumn
            account={account}
            index={index}
            actions={this.props.actions}
            removeIconVisible={!this.isLastAccount(index)}
            hasAccountChanged={this.hasAccountChanged(index, account)}
        />;
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
            this.addNewFtpAccount();
        }

        this.props.actions.updateInputValue(index, property, newValue);
    };

    addNewFtpAccount = () => {
        this.props.actions.addNewAccount({
            initialDir: 'public_html',
            password: '',
            port: this.props.defaultPorts.ftp,
            server: '',
            type: 'ftp',
            username: ''
        });
    };

    render() {
        return <Container>
            <form name={'ftpAccount'} autoComplete={'off'}>
                <table>
                    <thead>
                        {this.renderTableHeader()}
                    </thead>
                    <tbody>
                        {this.renderAccountRows()}
                    </tbody>
                </table>
            </form>
        </Container>;
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
