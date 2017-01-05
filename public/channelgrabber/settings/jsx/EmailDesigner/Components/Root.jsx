define([
    'react',
    'EmailDesigner/Components/ControlBar',
    'EmailDesigner/Components/TemplateView',
    'EmailDesigner/Components/ElementInspector',
    'Common/PubSub'
], function(
    React,
    ControlBar,
    TemplateView,
    ElementInspector,
    PubSub
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function() {
            return {
                editMode: false,
                template: {
                    name: 'Template Name',
                    elements: {}
                }
            }
        },
        componentDidMount: function() {
            //  Ajax request for email template if passed an id one

            //this.pubSubToken = PubSub.subscribe('ELEMENT.ADD', this.elementSubscriber);
        },
        componentWillUnmount: function () {
            PubSub.clearAllSubscriptions();
        },
        onTemplateNameChange: function (e) {
            var template = this.state.template;
            template.name = e.target.value;

            this.setState({
                template: template
            });
        },
        render: function() {
            return (
                <div className="email-designer-root">
                    <ControlBar template={this.state.template} onTemplateNameChange={this.onTemplateNameChange}/>
                    <TemplateView template={this.state.template}/>
                    <ElementInspector />
                </div>
            );
        }
    });

    return RootComponent;
});