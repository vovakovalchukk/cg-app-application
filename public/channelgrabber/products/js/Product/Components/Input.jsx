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
                initialValue: '',
                step: 1
            };
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

            var promise = this.props.submitCallback(this.props.name, this.state.newValue);
            promise.then(function(data) {
                this.setState({
                    editable: false,
                    oldValue: data.savedValue
                });
                console.log(data);
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
                <div className="detail-text-holder">
                    <div className="submit-input active">
                        <input type={this.props.type} className="submit-inputbox product-detail" onKeyPress={this.onKeyPress} onChange={this.onChange} value={this.state.newValue || this.props.initialValue} name={this.props.name} step={this.props.step} />
                        <div className="edit-btn" style={{display: (this.state.editable ? "none" : "inline-block")}}>
                            <ul>
                                <li onClick={this.editInput}><span className="edit"></span></li>
                            </ul>
                        </div>
                        <div className="submit-cancel" style={{display: (this.state.editable ? "inline-block" : "none")}}>
                            <ul>
                                <li onClick={this.submitInput}><span className="submit"></span></li>
                                <li onClick={this.cancelInput}><span className="cancel"></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            );
        }
    });

    return InputComponent;
});