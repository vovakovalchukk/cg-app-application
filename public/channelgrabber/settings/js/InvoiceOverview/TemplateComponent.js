define([
    'react'
], function(
    React
) {
    "use strict";

    var TemplateComponent = React.createClass({
        generateLinkElements: function() {
            var linkElements = [];
            if (this.props.links) {
                this.props.links.map(function (element) {
                    linkElements.push(React.createElement(
                        "div",
                        {className: 'invoice-template-action-link ' + element.name.toLowerCase()},
                        React.createElement("a", element.properties, element.name)
                    ));
                });
            }
            return linkElements;
        },
        render: function() {
            var linkElements = this.generateLinkElements();
            if (this.props.imageUrl) {
                var imageElement = React.createElement("img", {src: this.props.imageUrl});
            }
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