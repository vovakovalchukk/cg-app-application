define([
    'react'
], function(
    React
) {
    "use strict";

    var ControlBarComponent = React.createClass({
        getDefaultProps: function () {
            return {
                template: {
                    name: ''
                }
            }
        },
        onTemplateNameChange: function (e) {
            var newTemplate = this.props.template;
            newTemplate.name = e.target.value;
            this.props.onTemplateChange(newTemplate);
        },
        render: function()
        {
            return (
                <div className="sidebar sidebar-fixed sidebar-left sidebar-email-designer">
                    <div className="template-module email-action-buttons">
                        <a href="/settings" className="button">Back to Settings</a>
                    </div>
                    <div className="template-module">
                        <div className="heading-small">Template Name</div>
                        <div className="template-inputbox-holder">
                            <input
                                className="inputbox"
                                type="text"
                                value={this.props.template.name}
                                onChange={this.onTemplateNameChange}
                            />
                        </div>
                    </div>
                    <div className="template-module">
                        <div className="heading-small">Add Element</div>
                        <span className="button action">
                            <span className="icon sprite-sprite sprite-text-element-1520-black"></span>
                            <span className="title">Text</span>
                        </span>
                    </div>
                </div>
            );
        }
    });

    return ControlBarComponent;
});