define([
    'React',
    'EmailDesigner/Components/ControlBar',
    'EmailDesigner/Components/TemplateView',
    'EmailDesigner/Components/ElementInspector',
    'EmailDesigner/Components/ElementList',
    'Common/PubSub'
], function(
    React,
    ControlBar,
    TemplateView,
    ElementInspector,
    ElementList,
    PubSub
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function() {
            return {
                editMode: false,
                selectedElement: {
                    inspectors: []
                },
                template: {
                    name: 'Template Name',
                    elements: {}
                }
            }
        },
        componentDidMount: function() {
            //  Ajax request for email template if passed an id one

            PubSub.subscribe('ELEMENT', this.elementSubscriber);
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
        elementSubscriber: function (msg, data) {
            switch (msg) {
                case 'ELEMENT.ADD':
                    return this.addElement(data.type);
                case 'ELEMENT.SELECTED':
                    return this.selectElement(data);
                case 'ELEMENT.UPDATE':
                    return this.updateElement(data);
                case 'ELEMENT.DELETE':
                    return this.deleteElement(data);
            }
        },
        addElement: function (elementType) {
            var defaultData = ElementList.getDefaultData(elementType);

            var template = this.state.template;
            template.elements[defaultData.id] = defaultData;

            this.setState({
                template: template
            });
        },
        selectElement: function (element) {
            this.setState({
                selectedElement: this.state.template.elements[element.id]
            });
        },
        render: function() {
            console.log(this.state.selectedElement);
            return (
                <div className="email-designer-root">
                    <ControlBar template={this.state.template} onTemplateNameChange={this.onTemplateNameChange}/>
                    <TemplateView template={this.state.template}/>
                    <ElementInspector element={this.state.selectedElement}/>
                </div>
            );
        }
    });

    return RootComponent;
});