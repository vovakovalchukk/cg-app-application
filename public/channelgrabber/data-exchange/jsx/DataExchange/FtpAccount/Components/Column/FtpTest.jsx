import React from 'react';
import styled from "styled-components";

const InputContainer = styled.div`
    max-width: 150px;
    margin: 0 auto;
`;

class FtpTestColumn extends React.Component {
    static defaultProps = {
        onClick: () => {},
        hasAccountChanged: true
    };

    onClick = () => {
        if (this.props.hasAccountChanged) {
            return;
        }
        this.props.onClick();
    };

    getClassName = () => {
        return "button" + (this.props.hasAccountChanged ? ' disabled' : '');
    };

    render() {
        return (
            <InputContainer className={this.getClassName()} onClick={this.onClick}>
                <i className="fa fa-2x fa-wifi" aria-hidden="true"/>
                <span className="button-text">Test connection</span>
            </InputContainer>
        );
    }
}

export default FtpTestColumn;
