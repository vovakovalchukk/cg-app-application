define([
    'react'
], function(
    React
) {
    "use strict";

    var InputComponent = React.createClass({
        getDefaultProps: function() {
            return {
                inputType: 'input'
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
                    />
                </div>
            );
        }
    });

    return InputComponent;
});
