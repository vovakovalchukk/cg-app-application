define([
    'react',
    'EmailDesigner/Components/ControlBar',
    'EmailDesigner/Components/TemplateView',
    'EmailDesigner/Components/ElementInspector'
], function(
    React,
    ControlBar,
    TemplateView,
    ElementInspector
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function() {
            var emptyTemplate = {
                name: ''
            };
            return {
                template: emptyTemplate,
                oldTemplate: emptyTemplate,
                editMode: false
            }
        },
        componentDidMount: function() {
            //  Ajax request for email template if passed an id one

            var thisTemplate = {
                name: 'Template Name'
            };

            this.setState({
                template: thisTemplate,
                oldTemplate: thisTemplate
            });
        },
        onTemplateChange: function(newTemplate) {
            this.setState({
                template: newTemplate
            })
        },
        render: function() {
            return (
                <div>
                    <ControlBar template={this.state.template} onTemplateChange={this.onTemplateChange}/>
                    <TemplateView />
                    <ElementInspector />
                </div>
            );
        }
    });

    return RootComponent;
});