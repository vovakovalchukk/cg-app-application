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
            var ChannelForm = this.props.channelForm;
            return (
                <div className="category-form-container">
                    <h2>Category: {this.props.title}</h2>
                    <ChannelForm
                        categoryId={this.props.categoryId}
                        accountId={this.props.accountId}
                        variationsDataForProduct={this.props.variationsDataForProduct}
                        product={this.props.product}
                        fieldChange={this.props.fieldChange}
                        {...this.props.fieldValues}
                    />
                </div>
            );
        }
    });
    return CategoryFormComponent;
});