define([
    'react',
    'redux-form',
    './CategoryForm',
    './CategoryForm/Ebay'
], function(
    React,
    ReduxForm,
    CategoryForm,
    EbayForm
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
                categoryTemplates: {}
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
    return CategoryFormsComponent;
});