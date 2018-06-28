define([
    'react',
    'redux-form',
    './Amazon/ItemSpecifics',
    './Amazon/Subcategories'
], function(
    React,
    ReduxForm,
    ItemSpecifics,
    Subcategories
) {
    "use strict";

    var FormSection = ReduxForm.FormSection;

    var AmazonCategoryFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                categoryId: null,
                accountId: 0,
                itemSpecifics: {},
                rootCategories: {}
            };
        },
        render: function() {
            return (
                <div className="amazon-category-form-container">
                    <Subcategories rootCategories={this.props.rootCategories} accountId={this.props.accountId}/>
                    <FormSection
                        name="itemSpecifics"
                        component={ItemSpecifics}
                        categoryId={this.props.categoryId}
                        itemSpecifics={this.props.itemSpecifics}
                    />
                </div>
            );
        }
    });

    return AmazonCategoryFormComponent;
});
