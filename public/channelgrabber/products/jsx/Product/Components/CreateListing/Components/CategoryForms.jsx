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
                categoryTemplates: {}
            };
        },
        render: function() {
            var categoryTemplates = this.props.categoryTemplates;
            return (
                <div className="category-forms-container">
                    {Object.keys(categoryTemplates).map(function(categoryTemplateId) {
                        var categoryTemplate = categoryTemplates[categoryTemplateId];
                        for (var categoryId in categoryTemplate.categories) {
                            var category = categoryTemplate.categories[categoryId];
                            if (typeof channelToFormMap[category.channel] == 'undefined') {
                                continue;
                            }
                            var ChannelForm = channelToFormMap[category.channel];
                            return (<FormSection
                                name={'id-'+categoryId}
                                component={CategoryForm}
                                channelForm={ChannelForm}
                                {...category}
                            />);
                        }
                    })}
                </div>
            );
        }
    });
    return CategoryFormsComponent;
});