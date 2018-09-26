import React from 'react';
import BaseElement from 'EmailDesigner/Components/Elements/Base';


class TextComponent extends React.Component {
    static defaultProps = {
        text: ""
    };

    render() {

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
}

export default TextComponent;
