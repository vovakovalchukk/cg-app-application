define([
    'react',
    'Common/IdGenerator',
    'EmailDesigner/Components/ControlBar',
    'EmailDesigner/Components/TemplateView',
    'EmailDesigner/Components/ElementInspector'
], function(
    React,
    IdGenerator,
    ControlBar,
    TemplateView,
    ElementInspector
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function() {
            var emptyTemplate = {
                name: '',
                elements: []
            };
            return {
                latestElementId: 0,
                template: emptyTemplate,
                oldTemplate: emptyTemplate,
                editMode: false
            }
        },
        componentDidMount: function() {
            //  Ajax request for email template if passed an id one

            var thisTemplate = {
                name: 'Template Name',
                elements: []
            };

            this.setState({
                template: thisTemplate,
                oldTemplate: thisTemplate
            });
        },
        onTemplateChange: function(newTemplate) {
            this.setState({
                template: newTemplate
            });
        },
        onElementAdded: function (element) {
            var elementDefaults = {
                type: element,
                text: "Enter text...",
                id: String(IdGenerator.generate()),
                style: {
                    left: 0,
                    top: 0,
                    backgroundColour: null,
                    borderColour: "black",
                    borderWidth: null,
                    fontColour: "black",
                    fontFamily: "Arial",
                    fontSize: 12,
                    height: 90.708661417323,
                    lineHeight: 0,
                    padding: 0
                }
            };
            var thisTemplate = this.state.template;
            thisTemplate.elements.push(elementDefaults);
            this.setState({
                template: thisTemplate
            });
        },
        onElementSelected: function (element) {

        },
        render: function() {
            return (
                <div className="email-designer-root">
                    <ControlBar template={this.state.template} onTemplateChange={this.onTemplateChange} onElementSelected={this.onElementAdded}/>
                    <TemplateView template={this.state.template} onElementSelected={this.onElementSelected}/>
                    <ElementInspector />
                </div>
            );
        }
    });

    return RootComponent;
});