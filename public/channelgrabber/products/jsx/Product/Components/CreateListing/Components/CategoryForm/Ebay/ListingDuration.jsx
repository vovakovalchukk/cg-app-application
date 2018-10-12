import React from 'react';
import {Field} from 'redux-form';
import Select from 'Common/Components/Select';
import Validators from '../../../Validators';

class EbayListingDuration extends React.Component {
    static defaultProps = {
        listingDurations: {}
    };

    renderSelect = (field) => {
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
    };

    buildListingDurationOptions = (listingDurations) => {
        var options = [];
        for (var value in listingDurations) {
            options.push({
                "name": listingDurations[value],
                "value": value
            });
        }
        return options;
    };

    findSelectedOptionFromValue = (selectedValue, options) => {
        for (var key in options) {
            if (options[key].value == selectedValue) {
                return options[key];
            }
        }
        return null;
    };

    onOptionChange = (input, selectedOption) => {
        input.onChange(selectedOption.value);
    };

    render() {
        return <Field name="listingDuration" component={this.renderSelect} validate={Validators.required} />;
    }
}

export default EbayListingDuration;
