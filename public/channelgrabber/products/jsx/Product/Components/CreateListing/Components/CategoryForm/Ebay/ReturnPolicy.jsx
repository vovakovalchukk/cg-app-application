define([
    'react',
    'redux-form',
    'Common/Components/Select'
], function(
    React,
    ReduxForm,
    Select
) {
    "use strict";

    var Field = ReduxForm.Field;

    var EbayReturnPolicy = React.createClass({
        getDefaultProps: function() {
            return {
                returnPolicies: {}
            };
        },
        renderSelect: function(field) {
            var selectedOption = this.findSelectedOptionFromValue(field.input.value);
            return <label>
                <span className={"inputbox-label"}>Return Policy</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        name="returnPolicy"
                        options={this.props.returnPolicies}
                        autoSelectFirst={false}
                        title="Return Policy"
                        onOptionChange={this.onOptionChange.bind(this, field.input)}
                        selectedOption={selectedOption}
                    />
                </div>
            </label>;
        },
        findSelectedOptionFromValue: function(selectedValue) {
            var options = this.props.returnPolicies;
            for (var key in options) {
                if (options[key].value == selectedValue) {
                    return options[key];
                }
            }
            return null;
        },
        onOptionChange: function(input, selectedOption) {
            input.onChange(selectedOption.value);
        },
        render: function() {
            return <Field name="returnPolicy" component={this.renderSelect} />;
        }
    });
    return EbayReturnPolicy;
});