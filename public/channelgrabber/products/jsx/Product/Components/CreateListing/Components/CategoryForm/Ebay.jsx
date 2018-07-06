define([
    'react',
    'redux-form',
    './Ebay/AccountPolicy',
    './Ebay/ListingDuration',
    './Ebay/ItemSpecifics'
], function(
    React,
    ReduxForm,
    AccountPolicy,
    ListingDuration,
    ItemSpecifics
) {
    "use strict";

    let FormSection = ReduxForm.FormSection;

    let EbayCategoryFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                categoryId: null,
                listingDuration: {},
                itemSpecifics: {},
                returnPolicies: {},
                paymentPolicies: {},
                shippingPolicies: {},
                product: {},
                accountId: null,
                refreshAccountPolicies: () => {},
                accountData: {},
                setPoliciesForAccount: () => {}
            };
        },
        componentDidMount: function() {
            this.props.setPoliciesForAccount(this.props.accountId, {
                returnPolicies: this.props.returnPolicies,
                paymentPolicies: this.props.paymentPolicies,
                shippingPolicies: this.props.shippingPolicies,
            });
        },
        arePoliciesFetching: function() {
            return this.props.accountData.policies ? !!(this.props.accountData.policies.isFetching) : false;
        },
        getReturnPolicies: function() {
            let policies = this.props.accountData.policies;
            return {
                returnPolicies: (policies && policies.returnPolicies) ? policies.returnPolicies : [],
                paymentPolicies: (policies && policies.paymentPolicies) ? policies.paymentPolicies : [],
                shippingPolicies: (policies && policies.shippingPolicies) ? policies.shippingPolicies : [],
            }
        },
        render: function() {
            let policies = this.getReturnPolicies();
            return (
                <div className="ebay-category-form-container">
                    <AccountPolicy
                        {...policies}
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
