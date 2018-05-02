define([
    'react',
    'redux-form',
    './ChannelForm/Ebay'
], function(
    React,
    ReduxForm,
    EbayForm
) {
    "use strict";

    const channelToFormMap = {
        'ebay': EbayForm
    };

    var FormSection = ReduxForm.FormSection;

    var ChannelFormsComponent = React.createClass({
        getDefaultProps: function() {
            return {
                categoryTemplates: {}
            };
        },
        renderForCategoryTemplates: function() {
            var output = [];
            var channelsData = this.getChannelsDataFromCategoryTemplates(this.props.categoryTemplates);
            for (var channel in channelsData) {
                var channelData = channelsData[channel];
                var ChannelForm = channelToFormMap[channel];
                output.push(<FormSection
                    name={channel}
                    component={ChannelForm}
                    {...channelData.fieldValues}
                />);
            }
            return output;
        },
        getChannelsDataFromCategoryTemplates: function(categoryTemplates) {
            var channelsData = {};
            for (var categoryTemplateId in categoryTemplates) {
                var categoryTemplate = categoryTemplates[categoryTemplateId];
                for (var categoryId in categoryTemplate.categories) {
                    var category = categoryTemplate.categories[categoryId];
                    var channel = category.channel;
                    if (!this.isChannelSpecificFormPresent(channel)) {
                        continue;
                    }
                    channelsData[channel] = category;
                }
            }
            return channelsData;
        },
        isChannelSpecificFormPresent: function(channel) {
            return (typeof channelToFormMap[channel] != 'undefined');
        },
        render: function() {
            return (
                <div className="channel-forms-container">
                    {this.renderForCategoryTemplates()}
                </div>
            );
        }
    });
    return ChannelFormsComponent;
});