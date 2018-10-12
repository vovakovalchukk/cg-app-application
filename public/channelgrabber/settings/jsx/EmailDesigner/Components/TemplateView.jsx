import React from 'react';
import ElementList from 'EmailDesigner/Components/ElementList';
import PubSub from 'Common/PubSub';


class TemplateViewComponent extends React.Component {
    componentDidMount() {
        //PubSub.subscribe('ELEMENT.UPDATED', this.elementSubscriber);
    }

    componentWillUnmount() {
        //PubSub.unsubscribe(this.elementSubscriber);
    }

    elementSelected = (id) => {
        PubSub.publish('ELEMENT.SELECTED', {id: id});
    };

    renderElements = () => {
        var elements = [];
        var elementDataList = this.props.template.elements;
        for (var id in elementDataList) {
            if (!elementDataList.hasOwnProperty(id)) continue;
            elementDataList[id].onElementSelected = this.elementSelected;
            elements.push(ElementList.renderElement(elementDataList[id]));
        }
        return elements;
    };

    render() {
        return (
            <div className="template-view">
                {this.renderElements()}
            </div>
        );
    }
}

export default TemplateViewComponent;
