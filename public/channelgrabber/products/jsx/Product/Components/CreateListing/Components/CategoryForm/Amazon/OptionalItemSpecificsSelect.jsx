import React from 'react';
import Select from 'Common/Components/Select';
    

    var OptionalItemSpecificsSelect = React.createClass({
        getDefaultProps: function () {
            return {
                options: [],
                displayTitle: '',
                input: null
            };
        },
        onOptionalItemSpecificSelected: function (selected) {
            this.removeSelectedOptionFromOptions(selected);
            this.props.input.fields.push({
                fieldName: selected.value
            });
        },
        removeSelectedOptionFromOptions: function (selected) {
            var selectedOptionIndex = this.props.options.findIndex(option => {
                return option.value === selected.value;
            });
            if (selectedOptionIndex === -1) {
                return;
            }

            var newOptions = this.props.options.slice();
            newOptions.splice(selectedOptionIndex, 1);
        },
        render: function () {
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
    });

    export default OptionalItemSpecificsSelect;

