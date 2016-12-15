define([
    'react',
    'EmailDesigner/Components/ControlBar',
    'EmailDesigner/Components/EmailTemplate',
    'EmailDesigner/Components/ElementInspector'
], function(
    React,
    ControlBar,
    EmailTemplate,
    ElementInspector
) {
    "use strict";

    var RootComponent = React.createClass({
        getInitialState: function() {
            var emptyTemplate = {
                name: ''
            };
            return {
                template: emptyTemplate,
                oldTemplate: emptyTemplate,
                editMode: false
            }
        },
        render: function() {
            return (
                <div>
                    <ControlBar template={this.state.template} onTemplateChange={function(newTemplate){this.setState({template:newTemplate})}}/>
                    <EmailTemplate />
                    <ElementInspector />
                </div>
            );
        }
    });

    return RootComponent;
});