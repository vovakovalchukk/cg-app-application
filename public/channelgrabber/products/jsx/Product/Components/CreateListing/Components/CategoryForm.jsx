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
                channelForm: null
            };
        },
        render: function() {
            var ChannelForm = this.props.channelForm;
            return (
                <div className="category-form-container">
                    <h3>{this.props.title}</h3>
                    <ChannelForm {...this.props.fieldValues} />
                </div>
            );
        }
    });
    return CategoryFormComponent;
});