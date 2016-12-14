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
        getInitialState: function()
        {
            return {
                templateName: '',
                editMode: false
            }
        },
        render: function()
        {
            return (
                <div>
                    <ControlBar templateName={this.state.templateName} onTemplateNameChange={function(newName){this.setState({templateName:newName})}}/>
                    <EmailTemplate />
                    <ElementInspector />
                </div>
            );
        }
    });

    return RootComponent;
});