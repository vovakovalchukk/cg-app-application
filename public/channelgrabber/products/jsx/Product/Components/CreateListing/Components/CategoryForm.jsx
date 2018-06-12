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
                product: {}
            };
        },
        render: function() {
            var ChannelForm = this.props.channelForm;
            return (
                <div className="category-form-container">
                    <h2>Category: {this.props.title}</h2>
                    <ChannelForm
                        {...this.props.fieldValues}
                        categoryId={this.props.categoryId}
                        product={this.props.product}
                    />
                </div>
            );
        }
    });
    return CategoryFormComponent;
});