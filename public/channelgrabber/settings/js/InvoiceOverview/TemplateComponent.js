define([
    'React'
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
        generateImageElement: function()
        {
            if (!this.props.imageUrl) {
                return null;
            }
            var imageElement = React.createElement("img", {src: this.props.imageUrl});
            if (!this.props.links) {
                return imageElement;
            }
            this.props.links.forEach(function(element) {
                var linkName = element.name.toLowerCase();
                if (linkName == 'create' || linkName == 'edit') {
                    imageElement = React.createElement("a", element.properties, imageElement);
                    return false; // break
                }
            });
            return imageElement;
        },
        render: function() {
            var linkElements = this.generateLinkElements();
            var imageElement = this.generateImageElement();

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