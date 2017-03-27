define(['react', 'EmailDesigner/Components/Inspectors/Base'], function (React, BaseInspector) {
    "use strict";

    var DeleteComponent = React.createClass({
        displayName: 'DeleteComponent',

        render: function () {
            return React.createElement(
                BaseInspector,
                {
                    className: 'delete-inspector',
                    heading: this.props.heading
                },
                React.createElement(
                    'div',
                    { className: 'button', onClick: this.props.onAction },
                    React.createElement(
                        'span',
                        { className: 'title' },
                        'Delete'
                    )
                )
            );
        }
    });

    return DeleteComponent;
});
