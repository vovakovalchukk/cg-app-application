define([
    'react',
    'redux-form',
    'react-redux',
    './CategoryForm',
    './CategoryForm/Ebay',
    '../Actions/CreateListings/Actions'
], function(
    React,
    ReduxForm,
    ReactRedux,
    CategoryForm,
    EbayForm,
    Actions
) {
    "use strict";

    const channelToFormMap = {
        'ebay': EbayForm
    };

    var FormSection = ReduxForm.FormSection;

    var CategoryFormsComponent = React.createClass({
        getDefaultProps: function() {
            return {
                accounts: [],
                categoryTemplates: {},
                product: {},
                refreshAccountPolicies: () => {},
                accountsData: {},
                setReturnPoliciesForAccount: () => {}
            };
        },
        renderForCategoryTemplates: function() {
            var output = [];
            for (var categoryTemplateId in this.props.categoryTemplates) {
                var categoryTemplate = this.props.categoryTemplates[categoryTemplateId];
                output = output.concat(this.renderForCategoryTemplate(categoryTemplate))
            }
            return output;
        },
        renderForCategoryTemplate: function(categoryTemplate) {
            var output = [];
            for (var categoryId in categoryTemplate.categories) {
                var category = categoryTemplate.categories[categoryId];
                var categoryOutput = this.renderForCategory(category, categoryId);
                if (categoryOutput) {
                    output.push(categoryOutput);
                }
            }
            return output;
        },
        renderForCategory: function(category, categoryId) {
            if (!this.isAccountSelected(category.accountId)) {
                return null;
            }
            if (!this.isChannelSpecificFormPresent(category.channel)) {
                return null;
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
                {...category}
            />);
        },
        isAccountSelected: function(accountId) {
            return (this.props.accounts.indexOf(accountId) >= 0);
        },
        isChannelSpecificFormPresent: function(channel) {
            return (typeof channelToFormMap[channel] != 'undefined');
        },
        render: function() {
            return (
                <div className="category-forms-container">
                    {this.renderForCategoryTemplates()}
                </div>
            );
        }
    });

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
            setReturnPoliciesForAccount: function (accountId, returnPolicies) {
                dispatch(Actions.setReturnPoliciesForAccount(accountId, returnPolicies))
            }
        };
    };

    return ReactRedux.connect(mapStateToProps, mapDispatchToProps)(CategoryFormsComponent);
});