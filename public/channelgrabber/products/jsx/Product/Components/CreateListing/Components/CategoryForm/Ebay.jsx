define([
    'react',
    'redux-form',
    './Ebay/ReturnPolicy',
    './Ebay/ListingDuration',
    './Ebay/ItemSpecifics'
], function(
    React,
    ReduxForm,
    ReturnPolicy,
    ListingDuration,
    ItemSpecifics
) {
    "use strict";

    let FormSection = ReduxForm.FormSection;

    var EbayCategoryFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                categoryId: null,
                listingDuration: {},
                itemSpecifics: {},
                returnPolicies: {},
                product: {},
                accountId: null,
                refreshAccountPolicies: () => {}
            };
        },
        render: function() {
            return (
                <div className="ebay-category-form-container">
                    <ReturnPolicy
                        returnPolicies={this.props.returnPolicies}
                        accountId={this.props.accountId}
                        refreshAccountPolicies={this.props.refreshAccountPolicies}
                    />
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
