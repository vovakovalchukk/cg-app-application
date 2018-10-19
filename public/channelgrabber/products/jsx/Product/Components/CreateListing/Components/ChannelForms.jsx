import React from 'react';
import ReduxForm from 'redux-form';
import EbayForm from './ChannelForm/Ebay';
import AmazonForm from './ChannelForm/Amazon';


const channelToFormMap = {
    'ebay': EbayForm,
    'amazon': AmazonForm
};

var FormSection = ReduxForm.FormSection;

class ChannelFormsComponent extends React.Component {
    static defaultProps = {
        accounts: [],
        categoryTemplates: {},
        product: {},
        currency: null
    };

    renderForCategoryTemplates = () => {
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
    };

    getChannelsDataFromCategoryTemplates = (categoryTemplates) => {
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
    };

    isAccountSelected = (accountId) => {
        return (this.props.accounts.indexOf(accountId) >= 0);
    };

    isChannelSpecificFormPresent = (channel) => {
        return (typeof channelToFormMap[channel] != 'undefined');
    };

    render() {
        return (
            <div className="channel-forms-container">
                {this.renderForCategoryTemplates()}
            </div>
        );
    }
}

export default ChannelFormsComponent;
