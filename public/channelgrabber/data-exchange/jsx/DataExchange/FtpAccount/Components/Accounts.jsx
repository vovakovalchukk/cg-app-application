import React from 'react';
import {bindActionCreators} from 'redux';
import {connect} from 'react-redux';
import styled from "styled-components";
import Actions from "../Actions/Actions";
import TypeColumn from "./Column/Type";
import InputColumn from "./Column/Input";
import FtpTestColumn from "./Column/FtpTest";

const Container = styled.div`
    margin-top: 45px;
`;
const IconContainer = styled.span`
    cursor: pointer;
    margin-right: 10px;
`;
const TypeCellContainer = styled.td`
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
                <TypeCellContainer>{this.renderTypeColumn(account, index)}</TypeCellContainer>
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
        />
    };

    renderActionsColumn = (account, index) => {
        return <span>
            <IconContainer>
                <i
                    className={'fa fa-2x fa-check-square-o'}
                    aria-hidden="true"
                    onClick={this.props.actions.saveAccount.bind(this, index, account)}
                />
            </IconContainer>
            {!this.isLastAccount(index) &&
                <IconContainer>
                    <i
                        className={'fa fa-2x fa-trash-o'}
                        aria-hidden="true"
                        onClick={this.props.actions.removeAccount.bind(this, index, account)}
                    />
                </IconContainer>
            }
        </span>
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
