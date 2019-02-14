import React from 'react';
import SelectComponent from "Common/Components/Select";

class Select extends React.Component {
    defaultProps = {
        name: null,
        options: null,
        onChange: null
    };

    constructor(props) {
        super(props);
        this.state = {selected: props.selected || null};
    }

    select(selected) {
        this.setState(
            {selected: selected.value},
            () => {
                if (typeof this.props.onChange === "function") {
                    this.props.onChange(this.selected());
                }
            }
        )
    }

    selected() {
        return this.state.selected;
    }

    selectedOption() {
        let selected = this.selected();
        let selectedOption = this.options().find((option) => {
            return option.value === selected;
        });
        return selectedOption || {name: selected, value: selected};
    }

    options() {
        return this.props.options || [];
    }

    render() {
        return (
            <SelectComponent
                name={this.props.name}
                options={this.options()}
                selectedOption={this.selectedOption()}
                onOptionChange={(selected) => this.select(selected)}
            />
        );
    }
}

export default Select;