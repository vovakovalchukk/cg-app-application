define([
    'react'
], function(
    React
) {
    "use strict";

    var IncrementorInputComponent = React.createClass({
        getDefaultProps: function () {
            return {
                type: 'number',
                initialValue: ''
            };
        },
        getInitialState: function () {
            return {
                newValue: this.props.initialValue
            }
        },
        onChange: function (e) {
            var newValue = e.target.value;
            this.setState({
                newValue: newValue
            });
            this.props.submitCallback(newValue);
        },
        decrementValue: function (e) {
            var newValue = this.state.newValue - 1;
            if (newValue < 0) {
                newValue = 0;
            }
            this.setState({
                newValue: newValue
            });
            this.props.submitCallback(newValue);
        },
        incremementValue: function (e) {
            var newValue = this.state.newValue + 1;
            this.setState({
                newValue: newValue
            });
            this.props.submitCallback(newValue);
        },
        render: function () {
            return (
                <div className="incrementor-input-box">
                    <div className="incrementor-button" onClick={this.decrementValue}><span className="decrement">◄</span></div>
                    <input
                        type={this.props.type}
                        name={this.props.name}
                        onChange={this.onChange}
                        value={this.state.newValue || this.props.initialValue}
                        disabled={this.props.disabled ? 'disabled' : ''}
                        />
                    <div className="incrementor-button" onClick={this.incremementValue}><span className="increment">►</span></div>
                </div>
            );
        }
    });

    return IncrementorInputComponent;
});