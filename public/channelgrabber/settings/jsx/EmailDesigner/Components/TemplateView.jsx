define([
    'react',
    'EmailDesigner/Components/ElementList'
], function(
    React,
    ElementList
) {
    "use strict";

    var TemplateViewComponent = React.createClass({
        getDefaultProps: function () {
            return {
                template: {
                    name: '',
                    elements: []
                }
            }
        },
        createElement: function (element) {
            return ElementList.createElement(element);
        },
        render: function() {
            return (
                <div className="template-view">
                    {this.props.template.elements.map(function (element) {
                        return this.createElement(element);
                    }.bind(this))}
                </div>
            );
        }
    });

    return TemplateViewComponent;
});