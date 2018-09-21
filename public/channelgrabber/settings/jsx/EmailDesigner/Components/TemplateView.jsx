import React from 'react';
import ElementList from 'EmailDesigner/Components/ElementList';
import PubSub from 'Common/PubSub';
    

    var TemplateViewComponent = React.createClass({
        componentDidMount: function() {
            //PubSub.subscribe('ELEMENT.UPDATED', this.elementSubscriber);
        },
        componentWillUnmount: function () {
            //PubSub.unsubscribe(this.elementSubscriber);
        },
        elementSelected: function (id) {
            PubSub.publish('ELEMENT.SELECTED', {id: id});
        },
        renderElements: function () {
            var elements = [];
            var elementDataList = this.props.template.elements;
            for (var id in elementDataList) {
                if (!elementDataList.hasOwnProperty(id)) continue;
                elementDataList[id].onElementSelected = this.elementSelected;
                elements.push(ElementList.renderElement(elementDataList[id]));
            }
            return elements;
        },
        render: function() {
            return (
                <div className="template-view">
                    {this.renderElements()}
                </div>
            );
        }
    });

    export default TemplateViewComponent;
