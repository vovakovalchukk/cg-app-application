define([
    'react',
    'redux-form',
    './ChannelForm/Ebay',
    './ChannelForm/Amazon'
], function(
    React,
    ReduxForm,
    EbayForm,
    AmazonForm
) {
    "use strict";

    const channelToFormMap = {
        'ebay': EbayForm,
        'amazon': AmazonForm
    };

    var FormSection = ReduxForm.FormSection;

    var ChannelFormsComponent = React.createClass({
        getDefaultProps: function() {
            return {
                accounts: [],
                categoryTemplates: {},
                product: {},
                currency: null
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
                    product={this.props.product}
                    variationsDataForProduct={this.props.variationsDataForProduct}
                    currency={this.props.currency}
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
                    if (!this.isAccountSelected(category.accountId)) {
                        continue;
                    }
                    if (!this.isChannelSpecificFormPresent(category.channel)) {
                        continue;
                    }
                    channelsData[category.channel] = category;
                }
            }
            return channelsData;
        },
        isAccountSelected: function(accountId) {
            return (this.props.accounts.indexOf(accountId) >= 0);
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