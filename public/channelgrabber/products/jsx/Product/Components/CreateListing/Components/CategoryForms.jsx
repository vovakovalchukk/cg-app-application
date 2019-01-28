import React from 'react';
import {FormSection} from 'redux-form';
import {connect} from 'react-redux';
import CategoryForm from './CategoryForm';
import EbayForm from './CategoryForm/Ebay';
import Actions from '../Actions/CreateListings/Actions';
import AmazonForm from './CategoryForm/Amazon';

const channelToFormMap = {
    'ebay': EbayForm,
    'amazon': AmazonForm
};

class CategoryFormsComponent extends React.Component {
    static defaultProps = {
        accounts: [],
        categoryTemplates: {},
        product: {},
        refreshAccountPolicies: () => {},
        accountsData: {},
        setReturnPoliciesForAccount: () => {},
        variationsDataForProduct: [],
        fieldChange: null,
        resetSection: null,
        selectedProductDetails: {}
    };

    renderForCategoryTemplates = () => {
        var output = [];
        let catTemplates = {
            5: {
                accounts: {
                    6: {
                        accountId: 6,
                        channel: 'amazon',
                        categoryId: 13213
                    }
                }
            }
        };
        for (var categoryTemplateId in catTemplates) {
            var categoryTemplate = catTemplates[categoryTemplateId];
            output = output.concat(this.renderForCategoryTemplate(categoryTemplate))
        }
        return output;
    };

    renderForCategoryTemplate = (categoryTemplate) => {
        var output = [];
        for (var accountId in categoryTemplate.accounts) {
            var category = categoryTemplate.accounts[accountId];
            var categoryOutput = this.renderForCategory(category, category.categoryId);
            if (categoryOutput) {
                output.push(categoryOutput);
            }
        }
        return output;
    };

    renderForCategory = (category, categoryId) => {
        if (!this.isAccountSelected(category.accountId)) {
//            return null;
        }
        if (!this.isChannelSpecificFormPresent(category.channel)) {
//            return null;
        }

        var ChannelForm = channelToFormMap[category.channel];
        return (<FormSection
            name={categoryId}
            component={CategoryForm}
            channelForm={ChannelForm}
            categoryId={categoryId}
            product={this.props.product}
            refreshAccountPolicies={this.props.refreshAccountPolicies}
            accountId={category.accountId}
            accountData={this.props.accountsData[category.accountId]}
            setPoliciesForAccount={this.props.setPoliciesForAccount}
            variationsDataForProduct={this.props.variationsDataForProduct}
            fieldChange={this.props.fieldChange}
            resetSection={this.props.resetSection}
            selectedProductDetails={this.props.selectedProductDetails}
            {...category}
        />);
    };

    isAccountSelected = (accountId) => {
        return (this.props.accounts.indexOf(accountId) >= 0);
    };

    isChannelSpecificFormPresent = (channel) => {
        return (typeof channelToFormMap[channel] != 'undefined');
    };

    render() {
        console.log(this.props);
        return (
            <div className="category-forms-container">
                {this.renderForCategoryTemplates()}
            </div>
        );
    }
}

const mapStateToProps = function(state) {
    return {
        accountsData: state.accountsData
    };
};

const mapDispatchToProps = function(dispatch) {
    return {
        refreshAccountPolicies: function(accountId) {
            dispatch(Actions.refreshAccountPolicies(dispatch, accountId))
        },
        setPoliciesForAccount: function (accountId, policies) {
            dispatch(Actions.setPoliciesForAccount(accountId, policies))
        }
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(CategoryFormsComponent);
