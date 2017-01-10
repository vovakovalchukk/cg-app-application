define([
    'react'
], function(
    React
) {
    "use strict";

    var InputComponent = React.createClass({
        getDefaultProps: function () {
            return {
                type: 'number',
                initialValue: ''
            };
        },
        componentWillReceiveProps: function (newProps) {
            this.setState({
                newValue: newProps.initialValue,
                oldValue: newProps.initialValue
            });
        },
        getInitialState: function () {
            return {
                oldValue: this.props.initialValue,
                newValue: this.props.initialValue,
                editable: false,
                hover: false
            }
        },
        editInput: function () {
            this.setState({ editable: true });
        },
        cancelInput: function () {
            this.setState({
                editable: false,
                newValue: this.state.oldValue
            });
        },
        submitInput: function () {
            if (! this.state.editable) {
                return;
            }

            var promise = this.props.submitCallback(this.props.name, this.state.newValue || 0);
            promise.then(function(data) {
                this.setState({
                    editable: false,
                    newValue: data.savedValue
                });
            }.bind(this));
            promise.catch(function(error) {
                console.log(error.message);
            });
        },
        mouseOver: function () {
            this.setState({ hover: true });
        },
        mouseOut: function () {
            this.setState({ hover: false });
        },
        onChange: function (e) {
            this.setState({
                editable: true,
                newValue: e.target.value
            });
        },
        onKeyPress: function (e) {
            if (e.key === 'Enter') {
                this.submitInput();
            }
        },
        render: function () {
            return (
                <div className="safe-input-box">
                    <div className="submit-input" onFocus={this.editInput}>
                        <input
                            type={this.props.type}
                            onKeyPress={this.onKeyPress}
                            onChange={this.onChange}
                            value={this.state.newValue}
                            name={this.props.name}
                            disabled={this.props.disabled ? 'disabled' : ''}
                        />
                        <div className={"submit-cancel " + (this.state.editable ? "active" : "")}>
                            <div className="button-input" onClick={this.submitInput}><span className="submit"></span></div>
                            <div className="button-input" onClick={this.cancelInput}><span className="cancel"></span></div>
                        </div>
                    </div>
                </div>
            );
        }
    });

    return InputComponent;
});