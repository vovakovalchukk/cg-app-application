define([
    'react',
    'EmailDesigner/Components/Elements/Base'
], function(
    React,
    BaseElement
) {
    "use strict";

    var TextComponent = React.createClass({
        getDefaultProps: function() {
            return {
                text: ""
            };
        },
        render: function() {

            return (
                <BaseElement
                    className="text-element"
                    id={this.props.id}
                    style={this.props.style}
                    size={{width: 200, height: 50}}
                >
                    {this.props.text}
                </BaseElement>
            );
        }
    });

    return TextComponent;
});