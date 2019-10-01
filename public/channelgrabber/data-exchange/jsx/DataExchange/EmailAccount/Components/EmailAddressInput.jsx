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
        isVerifiable: false,
        verifiedStatus: null,
        isVerified: false,
        onVerifyClick: () => {}
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
            onChange={this.onChange.bind(this)}
        />;
    }

    onChange(input) {
        this.props.onChange(input.target.value);
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
