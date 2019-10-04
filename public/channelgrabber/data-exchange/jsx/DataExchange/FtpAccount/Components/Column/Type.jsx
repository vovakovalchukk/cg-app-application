import React from 'react';
import styled from "styled-components";
import SelectComponent from "Common/Components/Select";

class TypeColumn extends React.Component {
    static defaultProps = {
        account: {},
        options: {}
    };

    formatOptions = () => {
        return Object.keys(this.props.options).map(value => {
            let name = this.props.options[value];
            return {
                name,
                value
            }
        });
    };

    findSelectedOption = () => {
        let name = this.props.options[this.props.account.type];
        return {
            name: name,
            value: this.props.account.type
        }
    };

    render() {
        return <SelectComponent
            options={this.formatOptions()}
            selectedOption={this.findSelectedOption()}
        />;
    }
}

export default TypeColumn;
