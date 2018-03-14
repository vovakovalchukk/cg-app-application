define([
    'react',
], function(
    React
) {
    "use strict";

    var TodoComponent = React.createClass({
        getDefaultProps: function() {
            return {
                id: null,
                text: "",
                completed: false,
                onClick: null
            };
        },
        onClick: function() {
            if (!this.props.onClick) {
                return;
            }
            this.props.onClick(this.props.id);
        },
        render: function()
        {
            return (
                <li
                    onClick={this.onClick.bind(this)}
                    style={{
                        textDecoration: this.props.completed ? 'line-through' : 'none'
                    }}
                >
                    {this.props.text}
                </li>
            );
        }
    });

    return TodoComponent;
});