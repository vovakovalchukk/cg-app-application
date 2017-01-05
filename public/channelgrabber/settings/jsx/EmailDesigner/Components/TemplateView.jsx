define([
    'react',
    'EmailDesigner/Components/ElementList'
], function(
    React,
    ElementList
) {
    "use strict";

    var TemplateViewComponent = React.createClass({
        renderElements: function () {
            var elements = [];
            var elementDataList = this.props.template.elements;
            for (var id in elementDataList) {
                if (!elementDataList.hasOwnProperty(id)) continue;
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