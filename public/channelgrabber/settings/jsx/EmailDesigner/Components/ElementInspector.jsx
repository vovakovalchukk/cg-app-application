define([
    'react'
], function(
    React
) {
    "use strict";

    var EmailInspectorComponent = React.createClass({

        render: function()
        {
            return (
                <div className="sidebar sidebar-fixed sidebar-right sidebar-email-designer">
                    EmailInspector
                </div>
            );
        }
    });

    return EmailInspectorComponent;
});