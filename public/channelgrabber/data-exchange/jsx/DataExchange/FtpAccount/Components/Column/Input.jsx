import React from 'react';
import styled from "styled-components";

const Input = styled.input`
    width: auto;
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
        />;
    }
}

export default InputColumn;
