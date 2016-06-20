define([
    'react'
], function(
    React
) {
    "use strict";

    var TemplateComponent = React.createClass({
        getInitialState: function () {
            return { name: '', id: '', links: [] };
        },
        render: function() {
            console.log("Rendering Template");
            var output = React.createElement(
                "div",
                {className: 'invoice-template-element'},
                React.createElement(
                    "div",
                    {className: 'invoice-template-thumb'},
                    "Image Link"
                ),
                React.createElement(
                    "div",
                    {className: 'invoice-template-name'},
                    this.props.name
                ),
                React.createElement(
                    "div",
                    {className: 'invoice-template-actions'},
                    this.props.link.map(function(element){
                        React.createElement(
                            "div",
                            {className: 'invoice-template-action-link' + element.name},
                            React.createElement("a", {href: element.url}, element.name)
                        )
                    })
                )
            );
            return output;
        }
    });

    return TemplateComponent;
});