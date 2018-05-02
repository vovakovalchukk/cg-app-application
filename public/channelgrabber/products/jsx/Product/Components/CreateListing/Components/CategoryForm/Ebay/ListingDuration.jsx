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
                        title="ChannelGrabber recommends using GTC as this will allow us to automatically activate listings when you add new stock"
                    />
                </div>
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
            return <Field name="listingDuration" component={this.renderSelect} />;
        }
    });
    return EbayListingDuration;
});