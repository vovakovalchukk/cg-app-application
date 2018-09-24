import React from 'react';
import {Field} from 'redux-form';
import Select from 'Common/Components/Select';
import Validators from '../../../Validators';

    var EbayListingDuration = React.createClass({
        getDefaultProps: function() {
            return {
                listingDurations: {}
            };
        },
        renderSelect: function(field) {
            var options = this.buildListingDurationOptions(this.props.listingDurations);
            var selectedOption = this.findSelectedOptionFromValue(field.input.value, options);
            return <label>
                <span className={"inputbox-label"}>Listing Duration</span>
                <div className={"order-inputbox-holder"}>
                    <Select
                        name="listingDuration"
                        options={options}
                        autoSelectFirst={false}
                        title="Listing Duration"
                        onOptionChange={this.onOptionChange.bind(this, field.input)}
                        selectedOption={selectedOption}
                    />
                </div>
                {Validators.shouldShowError(field) && (
                    <span className="input-error">{field.meta.error}</span>
                )}
            </label>;
        },
        buildListingDurationOptions: function(listingDurations) {
            var options = [];
            for (var value in listingDurations) {
                options.push({
                    "name": listingDurations[value],
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
            return <Field name="listingDuration" component={this.renderSelect} validate={Validators.required} />;
        }
    });
    export default EbayListingDuration;
