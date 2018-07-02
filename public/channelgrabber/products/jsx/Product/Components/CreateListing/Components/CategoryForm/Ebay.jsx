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
                refreshAccountPolicies: () => {},
                accountData: {},
                setReturnPoliciesForAccount: () => {}
            };
        },
        componentDidMount: function() {
            this.props.setReturnPoliciesForAccount(this.props.accountId, this.props.returnPolicies);
        },
        arePoliciesFetching: function() {
            return this.props.accountData.policies ? !!(this.props.accountData.policies.isFetching) : false;
        },
        getReturnPolicies: function() {
            let policies = this.props.accountData.policies;
            if (policies && policies.returnPolicies) {
                return policies.returnPolicies;
            }
            return [];
        },
        render: function() {
            return (
                <div className="ebay-category-form-container">
                    <ReturnPolicy
                        returnPolicies={this.getReturnPolicies()}
                        accountId={this.props.accountId}
                        refreshAccountPolicies={this.props.refreshAccountPolicies}
                        disabled={this.arePoliciesFetching()}
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
