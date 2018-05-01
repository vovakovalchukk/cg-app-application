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
        getChannelsDataFromCategoryTemplates: function(categoryTemplates) {
            var channelsData = {};
            for (var categoryTemplateId in categoryTemplates) {
                var categoryTemplate = categoryTemplates[categoryTemplateId];
                for (var categoryId in categoryTemplate.categories) {
                    var category = categoryTemplate.categories[categoryId];
                    var channel = category.channel;
                    if (typeof channelToFormMap[channel] == 'undefined') {
                        continue;
                    }
                    channelsData[channel] = category;
                }
            }
            return channelsData;
        },
        render: function() {
            var channelsData = this.getChannelsDataFromCategoryTemplates(this.props.categoryTemplates);
            return (
                <div className="channel-forms-container">
                    {Object.keys(channelsData).map(function(channel) {
                        var channelData = channelsData[channel];
                        var ChannelForm = channelToFormMap[channel];
                        return (<FormSection
                            name={channel}
                            component={ChannelForm}
                            {...channelData.fieldValues}
                        />);
                    })}
                </div>
            );
        }
    });
    return ChannelFormsComponent;
});