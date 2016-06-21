define([
    'react'
], function(
    React
) {
    "use strict";

    var TemplateComponent = React.createClass({
        render: function() {
            var linkElements = [];
            this.props.links.map(function(element){
                linkElements.push(React.createElement(
                    "div",
                    {className: 'invoice-template-action-link ' + element.name},
                    React.createElement("a", {href: element.url, target: '_blank'}, element.name)
                ));
            });
            var imageElement = React.createElement("img", {src: this.props.imageUrl});
            var output = React.createElement(
                "div",
                {className: 'invoice-template-element'},
                React.createElement("div", {className: 'invoice-template-thumb'}, imageElement),
                React.createElement("div", {className: 'invoice-template-name'}, this.props.name),
                React.createElement("div", {className: 'invoice-template-actions'}, linkElements)
            );
            return output;
        }
    });

    return TemplateComponent;
});