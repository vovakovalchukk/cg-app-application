define([
    'react',
], function(
    React
) {
    "use strict";

    var FilterLinkComponent = React.createClass({
        getDefaultProps: function() {
            return {
                filter: "",
                active: false,
                onClick: null
            };
        },
        onClick: function(e) {
            e.preventDefault();
            if (!this.props.onClick) {
                return;
            }
            this.props.onClick(this.props.filter);
        },
        render: function()
        {
            if (this.props.active) {
                return (<span>{this.props.children}</span>);
            }
            return (
                <a href="#"
                   onClick={this.onClick.bind(this)}
                >
                    {this.props.children}
                </a>
            );
        }
    });

    return FilterLinkComponent;
});