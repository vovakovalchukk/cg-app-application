define([
    'react'
], function(
    React
) {
    "use strict";

    var InputComponent = React.createClass({
        getDefaultProps: function() {
            return {
                inputType: 'input',
                title: null
            }
        },
        mouseOver: function () {
            this.setState({ hover: true });
        },
        mouseOut: function () {
            this.setState({ hover: false });
        },
        render: function () {
            return (
                <div className="safe-input-box">
                    <input
                        type={this.props.inputType}
                        name={this.props.name}
                        value={this.props.value}
                        onChange={this.props.onChange}
                        title={this.props.title}
                    />
                </div>
            );
        }
    });

    return InputComponent;
});
