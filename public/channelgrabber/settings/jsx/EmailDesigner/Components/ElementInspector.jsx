import React from 'react';
import InspectorList from 'EmailDesigner/Components/InspectorList';
import PubSub from 'Common/PubSub';
    

    var ElementInspectorComponent = React.createClass({
        getDefaultProps: function () {
            return {
                element: {
                    inspectors: []
                }
            }
        },
        componentDidMount: function() {
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
        render: function()
        {
            return (
                <div className="sidebar sidebar-fixed sidebar-right sidebar-email-designer">
                    {this.props.element.inspectors.map(function (inspectorName) {
                        return InspectorList.renderInspector({type: inspectorName, element: this.props.element});
                    }.bind(this))}
                </div>
            );
        }
    });

    export default ElementInspectorComponent;
