import React from 'react';
import styled from "styled-components";
import ButtonComponent from "Common/Components/Button";

const EmailInputContainer = styled.div`
    display: flex;
    align-items: center;
    padding: 0 10px;
`;

const InputContainer = styled.input`
    float: none;
    outline: none;
`;

const ButtonContainer = styled.div`
    margin-left: 10px;
    width: 80px;
`;

const STATUS_PENDING = 'Pending';
const STATUS_FAILED = 'Failed';
const STATUS_TEMP_FAILURE = 'TemporaryFailure';
const STATUS_VERIFIED = 'Success';

const STATUS = {
    pending: STATUS_PENDING,
    failed: STATUS_FAILED,
    tempFailure: STATUS_TEMP_FAILURE,
    verified: STATUS_VERIFIED,
};

class EmailAddressInputComponent extends React.Component {
    static defaultProps = {
        name: '',
        value: '',
        placeholder: '',
        onChange: () => {},
        onKeyPressEnter: () => {},
        isVerifiable: false,
        verifiedStatus: null,
        isVerified: false,
        onVerifyClick: () => {},
        type: 'text'
    };

    render() {
        return <EmailInputContainer>
            {this.renderInput()}
            {this.renderVerificationStatus()}
        </EmailInputContainer>;
    }

    renderInput() {
        return <InputContainer
            value={this.props.value}
            name={this.props.name}
            placeholder={this.props.placeholder}
            type={this.props.type ? this.props.type : 'text'}
            onChange={this.onChange.bind(this)}
            onKeyPress={this.onKeyPress.bind(this)}
        />;
    }

    onChange(event) {
        this.props.onChange(event.target.value);
    }

    onKeyPress(event) {
        if (event.key !== "Enter") {
            return;
        }

        this.props.onKeyPressEnter(this.props.value);
    }

    renderVerificationStatus() {
        if (!this.props.isVerifiable) {
            return null;
        }

        if (!this.props.isVerified) {
            return this.renderVerifyButton();
        }

        return this.renderVerificationStatusLabel();
    }

    renderVerifyButton() {
        return <ButtonContainer>
            <ButtonComponent
                text={"Verify"}
                onClick={this.props.onVerifyClick}
            />
        </ButtonContainer>;
    }

    renderVerificationStatusLabel() {
        return <span>{this.props.verifiedStatus}</span>;
    }
}

export default EmailAddressInputComponent;
