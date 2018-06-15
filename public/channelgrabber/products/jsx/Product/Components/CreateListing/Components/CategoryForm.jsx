define([
    'react'
], function(
    React
) {
    "use strict";

    var CategoryFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                title: null,
                fieldValues: {},
                channelForm: null,
                categoryId: null,
                accountId: null,
                product: {},
                variationsDataForProduct: []
            };
        },
        render: function() {
            console.log('in CategoryForm render method');
            
            
            var ChannelForm = this.props.channelForm;
            return (
                <div className="category-form-container">
                    <h2>Category: {this.props.title}</h2>
                    <ChannelForm
                        categoryId={this.props.categoryId}
                        accountId={this.props.accountId}
                        variationsDataForProduct={this.props.variationsDataForProduct}
                        product={this.props.product}
                        {...this.props.fieldValues}
                    />
                </div>
            );
        }
    });
    return CategoryFormComponent;
});