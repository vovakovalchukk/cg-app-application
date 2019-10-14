import React from 'react';
import styled from "styled-components";

const Input = styled.input`
    width: auto;
    max-width: 70%;
`;

class InputColumn extends React.Component {
    static defaultProps = {
        account: {},
        index: 0,
        type: 'text',
        property: '',
        placeholder: '',
        updateInputValue: () => {}
    };

    onChange = (event) => {
        this.props.updateInputValue(this.props.index, this.props.property, event.target.value);
    };

    getPropertyValueFromAccount = () => {
        let value = this.props.account[this.props.property];
        return value === null ? '' : value;
    };

    render() {
        return <Input
            value={this.getPropertyValueFromAccount()}
            name={this.props.property + this.props.index}
            placeholder={this.props.placeholder}
            type={this.props.type}
            onChange={this.onChange}
            autoComplete={this.props.type === 'password' ? 'new-password' : 'off'}
            // We need to render the field as read-only then make it editable on focus to prevent browsers from auto-filling
            // the username and password in the FTP account table
            readOnly={true}
            onFocus={(event) => {event.target.removeAttribute('readonly')}}
        />;
    }
}

export default InputColumn;
