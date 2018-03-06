define([
    'react'
], function(
    React
) {
    "use strict";

    var RootComponent = React.createClass({
        getDefaultProps: function() {
            return {
            };
        },
        getInitialState: function()
        {
            return {
            };
        },
        render: function()
        {
            return (
                <div>Hello World!</div>
            );
        }
    });

    return RootComponent;
});