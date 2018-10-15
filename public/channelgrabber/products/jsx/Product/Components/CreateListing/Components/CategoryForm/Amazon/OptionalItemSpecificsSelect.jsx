import React from 'react';
import Select from 'Common/Components/Select';


class OptionalItemSpecificsSelect extends React.Component {
    static defaultProps = {
        options: [],
        displayTitle: '',
        input: null
    };

    onOptionalItemSpecificSelected = (selected) => {
        this.removeSelectedOptionFromOptions(selected);
        this.props.input.fields.push({
            fieldName: selected.value
        });
    };

    removeSelectedOptionFromOptions = (selected) => {
        var selectedOptionIndex = this.props.options.findIndex(option => {
            return option.value === selected.value;
        });
        if (selectedOptionIndex === -1) {
            return;
        }

        var newOptions = this.props.options.slice();
        newOptions.splice(selectedOptionIndex, 1);
    };

    render() {
        if (this.props.options.length === 0) {
            return null;
        }
        return <label>
            <span className={"inputbox-label"}><b>{this.props.displayTitle}</b></span>
            <div className={"order-inputbox-holder"}>
                <Select
                    name="optionalItemSpecifics"
                    options={this.props.options}
                    autoSelectFirst={false}
                    onOptionChange={this.onOptionalItemSpecificSelected}
                    filterable={true}
                />
            </div>
        </label>
    }
}

export default OptionalItemSpecificsSelect;

