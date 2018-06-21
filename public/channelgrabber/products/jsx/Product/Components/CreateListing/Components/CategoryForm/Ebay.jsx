define([
    'react',
    'redux-form',
    './Ebay/ListingDuration',
    './Ebay/ItemSpecifics'
], function(
    React,
    ReduxForm,
    ListingDuration,
    ItemSpecifics
) {
    "use strict";

    var FormSection = ReduxForm.FormSection;

    var EbayCategoryFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                categoryId: null,
                listingDuration: {},
                itemSpecifics: {},
                product: {}
            };
        },
        render: function() {
            return (
                <div className="ebay-category-form-container">
                    <ListingDuration listingDurations={this.props.listingDuration} />
                    <FormSection
                        name="itemSpecifics"
                        component={ItemSpecifics}
                        categoryId={this.props.categoryId}
                        itemSpecifics={this.props.itemSpecifics}
                        product={this.props.product}
                    />
                </div>
            );
        }
    });
    return EbayCategoryFormComponent;
});
