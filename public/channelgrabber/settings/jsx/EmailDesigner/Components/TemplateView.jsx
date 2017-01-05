define([
    'react',
    'EmailDesigner/Components/ElementList',
    'Common/PubSub'
], function(
    React,
    ElementList,
    PubSub
) {
    "use strict";

    var TemplateViewComponent = React.createClass({
        componentDidMount: function() {
            //  Ajax request for email template if passed an id one

            this.pubSubToken = PubSub.subscribe('ELEMENT.UPDATED', this.elementSubscriber);
        },
        componentWillUnmount: function () {
            PubSub.clearAllSubscriptions();
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

    return TemplateViewComponent;
});