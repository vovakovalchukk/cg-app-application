define([
    'react',
    'Common/Components/Select'
], function(
    React,
    Select
) {
    "use strict";

    var OptionalItemSpecificsSelect = React.createClass({
        getDefaultProps: function () {
            return {
                options: [],
                displayTitle: '',
                input: null
            };
        },
        getInitialState: function () {
            return {
                options: []
            };
        },
        componentDidMount: function() {
            this.setState({
                options: this.props.options
            });
        },
        componentWillReceiveProps(nextProps) {
            if (JSON.stringify(nextProps.options) === JSON.stringify(this.props.options)) {
                return;
            }

            this.setState({
                options: nextProps.options
            });
        },
        onOptionalItemSpecificSelected: function (selected) {
            this.removeSelectedOptionFromOptions(selected);
            this.props.input.fields.push({
                fieldName: selected.value
            });
        },
        removeSelectedOptionFromOptions: function (selected) {
            var selectedOptionIndex = this.state.options.findIndex(option => {
                return option.value === selected.value;
            });
            if (selectedOptionIndex === -1) {
                return;
            }

            var newOptions = this.state.options.slice();
            newOptions.splice(selectedOptionIndex, 1);
            this.setState({
                options: newOptions
            });
        },
        render: function () {
            if (this.state.options.length === 0) {
                return null;
            }
            return <label>
                <span className={"inputbox-label"}><b>{this.props.displayTitle}</b></span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        name="optionalItemSpecifics"
                        options={this.state.options}
                        autoSelectFirst={false}
                        onOptionChange={this.onOptionalItemSpecificSelected}
                        filterable={true}
                    />
                </div>
            </label>
        }
    });

    return OptionalItemSpecificsSelect;
});
