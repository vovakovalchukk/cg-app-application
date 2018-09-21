import React from 'react';
import BaseElement from 'EmailDesigner/Components/Elements/Base';
    

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

    export default TextComponent;
