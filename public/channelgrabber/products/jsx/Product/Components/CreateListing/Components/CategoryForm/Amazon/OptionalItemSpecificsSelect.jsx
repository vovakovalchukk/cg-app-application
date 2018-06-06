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
                options: {},
                displayTitle: '',
                input: null
            };
        },
        onOptionalItemSpecificSelected: function (selected) {
            this.props.input.fields.push({
                fieldName: selected.value
            });
        },
        render: function () {
            return <label>
                <span className={"inputbox-label"}>{this.props.displayTitle}</span>
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

    return OptionalItemSpecificsSelect;
});
