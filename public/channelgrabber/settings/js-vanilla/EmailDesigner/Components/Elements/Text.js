define(['react', 'EmailDesigner/Components/Elements/Base'], function (React, BaseElement) {
    "use strict";

    var TextComponent = React.createClass({
        displayName: 'TextComponent',

        getDefaultProps: function () {
            return {
                text: ""
            };
        },
        render: function () {

            return React.createElement(
                BaseElement,
                {
                    className: 'text-element',
                    id: this.props.id,
                    onElementSelected: this.props.onElementSelected,
                    style: this.props.style,
                    size: this.props.size
                },
                this.props.text
            );
        }
    });

    return TextComponent;
});
