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
                    size={{width: this.props.width, height: this.props.height}}
                >
                    {this.props.text}
                </BaseElement>
            );
        }
    });

    return TextComponent;
});