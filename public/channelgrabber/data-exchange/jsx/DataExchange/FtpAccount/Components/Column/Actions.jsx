import React from 'react';
import styled from "styled-components";

const IconContainer = styled.span`
    cursor: ${props => props.disabled ? 'not-allowed' : 'pointer'};
    color: ${props => props.disabled ? 'lightgrey' : 'black'};
    margin-right: 10px;
    visibility: ${props => props.isHidden ? 'hidden' : 'visible'};
`;

class ActionsColumn extends React.Component {
    static defaultProps = {
        account: {},
        index: 0,
        actions: {},
        removeIconVisible: true,
        hasAccountChanged: false
    };

    renderSaveIcon = () => {
        return <IconContainer disabled={!this.props.hasAccountChanged}>
            <i
                className={'fa fa-2x fa-check-square-o'}
                aria-hidden="true"
                onClick={this.props.hasAccountChanged ? this.props.actions.saveAccount.bind(this, this.props.index, this.props.account) :  () => {}}
                title={'Save'}
            />
        </IconContainer>;
    };


    renderRemoveIcon = () => {
        return <IconContainer isHidden={!this.props.removeIconVisible}>
            <i
                className={'fa fa-2x fa-trash-o'}
                aria-hidden="true"
                onClick={this.props.actions.removeAccount.bind(this, this.props.index, this.props.account)}
                title={'Delete'}
            />
        </IconContainer>;
    };

    render() {
        return <span>
            {this.renderSaveIcon()}
            {this.renderRemoveIcon()}
        </span>
    }
}

export default ActionsColumn;
