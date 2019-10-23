import React from 'react';
import styled from "styled-components";
import SelectComponent from "Common/Components/Select";

class TypeColumn extends React.Component {
    static defaultProps = {
        account: {},
        index: 0,
        options: {},
        updateInputValue: () => {}
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

    onOptionsChange = (option) => {
        this.props.updateInputValue(this.props.index, 'type', option.value);
    };

    componentDidMount = () => {
        // This is hack to force a re-render of the Select Component. If we don't do this, then the selected option doesn't work on the first render
        this.props.updateInputValue(this.props.index, 'type', this.props.account.type);
    };

    render() {
        return <SelectComponent
            options={this.formatOptions()}
            selectedOption={this.findSelectedOption()}
            onOptionChange={this.onOptionsChange}
        />;
    }
}

export default TypeColumn;
