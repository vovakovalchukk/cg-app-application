define([
    'react',
    'EmailDesigner/Components/Elements/Text'
], function(
    React,
    Text
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
            switch (element.type) {
                case 'Text':
                    return (<Text id={element.id} text={element.text} initialPosition={{x: element.x, y: element.y}}/>);
                default:
                    return
            }
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