define([
    'React',
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
                    onElementSelected={this.props.onElementSelected}
                    style={this.props.style}
                    size={this.props.size}
                >
                    {this.props.text}
                </BaseElement>
            );
        }
    });

    return TextComponent;
});