define(['react', 'EmailDesigner/Components/InspectorList', 'Common/PubSub'], function (React, InspectorList, PubSub) {
    "use strict";

    var ElementInspectorComponent = React.createClass({
        displayName: 'ElementInspectorComponent',

        getDefaultProps: function () {
            return {
                element: {
                    inspectors: []
                }
            };
        },
        componentDidMount: function () {
            //PubSub.subscribe('ELEMENT.SELECTED', this.elementSubscriber);
        },
        componentWillUnmount: function () {
            //PubSub.unsubscribe(this.elementSubscriber);
        },
        elementSubscriber: function (msg, data) {
            // this.setState({
            //     inspectors: data.inspectors
            // });
        },
        render: function () {
            return React.createElement(
                'div',
                { className: 'sidebar sidebar-fixed sidebar-right sidebar-email-designer' },
                this.props.element.inspectors.map(function (inspectorName) {
                    return InspectorList.renderInspector({ type: inspectorName, element: this.props.element });
                }.bind(this))
            );
        }
    });

    return ElementInspectorComponent;
});
