import React from 'react';
import ControlBar from 'EmailDesigner/Components/ControlBar';
import TemplateView from 'EmailDesigner/Components/TemplateView';
import ElementInspector from 'EmailDesigner/Components/ElementInspector';
import ElementList from 'EmailDesigner/Components/ElementList';
import PubSub from 'Common/PubSub';


class RootComponent extends React.Component {
    state = {
        editMode: false,
        selectedElement: {
            inspectors: []
        },
        template: {
            name: 'Template Name',
            elements: {}
        }
    };

    componentDidMount() {
        //  Ajax request for email template if passed an id one

        PubSub.subscribe('ELEMENT', this.elementSubscriber);
    }

    componentWillUnmount() {
        PubSub.clearAllSubscriptions();
    }

    onTemplateNameChange = (e) => {
        var template = this.state.template;
        template.name = e.target.value;

        this.setState({
            template: template
        });
    };

    elementSubscriber = (msg, data) => {
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
    };

    addElement = (elementType) => {
        var defaultData = ElementList.getDefaultData(elementType);

        var template = this.state.template;
        template.elements[defaultData.id] = defaultData;

        this.setState({
            template: template
        });
    };

    selectElement = (element) => {
        this.setState({
            selectedElement: this.state.template.elements[element.id]
        });
    };

    render() {
        return (
            <div className="email-designer-root">
                <ControlBar template={this.state.template} onTemplateNameChange={this.onTemplateNameChange}/>
                <TemplateView template={this.state.template}/>
                <ElementInspector element={this.state.selectedElement}/>
            </div>
        );
    }
}

export default RootComponent;
