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
                value: 0,
                step: 1
            };
        },
        getInitialState: function () {
            return {
                oldValue: this.props.value,
                newValue: this.props.value,
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
            //  call submit callback (onSuccess: oldValue = newValue)
            this.props.submitCallback(this.props.name, this.state.newValue);
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
                        <input type={this.props.type} className="submit-inputbox product-detail" onKeyPress={this.onKeyPress} onChange={this.onChange} value={this.state.newValue} name={this.props.name} step={this.props.step} />
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