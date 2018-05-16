define([
    'react',
    'redux-form',
    './Amazon/ItemSpecifics'
], function(
    React,
    ReduxForm,
    ItemSpecifics
) {
    "use strict";

    var FormSection = ReduxForm.FormSection;

    var AmazonCategoryFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                categoryId: null,
                itemSpecifics: {}
            };
        },
        render: function() {
            return (
                <div className="ebay-category-form-container">
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
