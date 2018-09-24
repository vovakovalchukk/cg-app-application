import React from 'react';
import {FormSection} from 'redux-form';
import AccountPolicy from './Ebay/AccountPolicy';
import ListingDuration from './Ebay/ListingDuration';
import ItemSpecifics from './Ebay/ItemSpecifics';

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
                setPoliciesForAccount: () => {},
                selectedProductDetails: {}
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
        renderItemSpecifics: function() {
            if (this.props.selectedProductDetails && Object.keys(this.props.selectedProductDetails).length > 0) {
                return null;
            }
            return <FormSection
                name="itemSpecifics"
                component={ItemSpecifics}
                categoryId={this.props.categoryId}
                itemSpecifics={this.props.itemSpecifics}
                product={this.props.product}
            />;
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
                    {this.renderItemSpecifics()}
                </div>
            );
        }
    });
    export default EbayCategoryFormComponent;

