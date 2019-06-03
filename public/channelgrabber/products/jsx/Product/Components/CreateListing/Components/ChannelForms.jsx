import React from 'react';
import {FormSection} from 'redux-form';
import EbayForm from './ChannelForm/Ebay';
import AmazonForm from './ChannelForm/Amazon';
import {ProductContext} from "../../Root";

const channelFormMap = {
    'ebay': EbayForm,
    'amazon': AmazonForm
};

let productContextProps = null;

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
            var ChannelForm = channelFormMap[channel];
            output.push(<FormSection
                name={channel}
                component={ChannelForm}
                product={this.props.product}
                variationsDataForProduct={this.props.variationsDataForProduct}
                currency={this.props.currency}
                productContextProps={productContextProps}
                {...channelData.fieldValues}
            />);
        }
        return output;
    };

    getChannelsDataFromCategoryTemplates = (categoryTemplates) => {
        var channelsData = {};
        for (var categoryTemplateId in categoryTemplates) {
            var categoryTemplate = categoryTemplates[categoryTemplateId];

            for (let accountId in categoryTemplate.accounts){
                let categoryAccount = categoryTemplate.accounts[accountId]

                if (!this.isAccountSelected(accountId)) {
                    continue;
                }
                if (!this.isChannelSpecificFormPresent(categoryAccount.channel)) {
                    continue;
                }
                channelsData[categoryAccount.channel] = categoryAccount;
            }
        }
        return channelsData;
    };

    isAccountSelected = (accountId) => {
        return (this.props.accounts.indexOf(parseInt(accountId)) >= 0);
    };

    isChannelSpecificFormPresent = (channel) => {
        return (typeof channelFormMap[channel] != 'undefined');
    };

    renderInContext = (contextValue) => {
        productContextProps = contextValue;
        return (
            <div className="channel-forms-container">
                {this.renderForCategoryTemplates()}
            </div>
        )
    };

    render() {
        return (
            <ProductContext.Consumer>
                {this.renderInContext}
            </ProductContext.Consumer>
        );
    }
}

export default ChannelFormsComponent;
