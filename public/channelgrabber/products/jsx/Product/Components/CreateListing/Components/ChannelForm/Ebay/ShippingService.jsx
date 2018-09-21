import React from 'react';
import ReduxForm from 'redux-form';
import Select from 'Common/Components/Select';
import Validators from '../../../Validators';
    

    var Field = ReduxForm.Field;

    var EbayShippingService = React.createClass({
        getDefaultProps: function() {
            return {
                shippingServices: {}
            };
        },
        renderSelect: function(field) {
            var options = this.buildShippingServiceOptions(this.props.shippingServices);
            var selectedOption = this.findSelectedOptionFromValue(field.input.value, options);
            return <label>
                <span className={"inputbox-label"}>Shipping Service</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        name="shippingMethod"
                        options={options}
                        autoSelectFirst={false}
                        title="Shipping Service"
                        onOptionChange={this.onOptionChange.bind(this, field.input)}
                        selectedOption={selectedOption}
                        className={Validators.shouldShowError(field) ? 'error' : null}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
            </label>;
        },
        buildShippingServiceOptions: function(shippingServices) {
            var options = [];
            for (var value in shippingServices) {
                options.push({
                    "name": shippingServices[value],
                    "value": value
                });
            }
            return options;
        },
        findSelectedOptionFromValue: function(selectedValue, options) {
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
            return <Field name="shippingMethod" component={this.renderSelect} validate={Validators.required} />;
        }
    });
    export default EbayShippingService;
