define(['react', 'Common/PubSub'], function (React, PubSub) {
    "use strict";

    var ControlBarComponent = React.createClass({
        displayName: 'ControlBarComponent',

        getDefaultProps: function () {
            return {
                template: {
                    name: '',
                    elements: []
                }
            };
        },
        onElementSelected: function (elementType) {
            PubSub.publish('ELEMENT.ADD', { type: elementType });
        },
        render: function () {
            return React.createElement(
                'div',
                { className: 'sidebar sidebar-fixed sidebar-left sidebar-email-designer' },
                React.createElement(
                    'div',
                    { className: 'template-module email-action-buttons' },
                    React.createElement(
                        'a',
                        { href: '/settings', className: 'button' },
                        'Back to Settings'
                    )
                ),
                React.createElement(
                    'div',
                    { className: 'template-module' },
                    React.createElement(
                        'div',
                        { className: 'heading-small' },
                        'Template Name'
                    ),
                    React.createElement(
                        'div',
                        { className: 'template-inputbox-holder' },
                        React.createElement('input', {
                            className: 'inputbox',
                            type: 'text',
                            value: this.props.template.name,
                            onChange: this.props.onTemplateNameChange
                        })
                    )
                ),
                React.createElement(
                    'div',
                    { className: 'template-module' },
                    React.createElement(
                        'div',
                        { className: 'heading-small' },
                        'Add Element'
                    ),
                    React.createElement(
                        'span',
                        { className: 'button action', onClick: this.onElementSelected.bind(this, 'Text') },
                        React.createElement('span', { className: 'icon sprite-sprite sprite-text-element-1520-black' }),
                        React.createElement(
                            'span',
                            { className: 'title' },
                            'Text'
                        )
                    )
                )
            );
        }
    });

    return ControlBarComponent;
});
