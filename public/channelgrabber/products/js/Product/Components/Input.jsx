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
            this.setState({ editable: false });
        },
        submitInput: function () {
            if (this.state.editable) {
                //  call submit callback
                console.log('Submitting Input as '+this.state.newValue);
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
                <div className="detail-text-holder">
                    <div className="submit-input active">
                        <input type={this.props.type} className="submit-inputbox product-detail" placeholder="" value={this.props.value} name={this.props.name} step={this.props.step} />
                        <div className="edit-btn" style={{display: (this.state.editable ? "none" : "inline-block")}}>
                            <ul>
                                <li><span className="edit" onClick={this.editInput}></span></li>
                            </ul>
                        </div>
                        <div className="submit-cancel" style={{display: (this.state.editable ? "inline-block" : "none")}}>
                            <ul>
                                <li><span className="submit" onClick={this.submitInput}></span></li>
                                <li><span className="cancel" onClick={this.cancelInput}></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            );
        }
    });

    return InputComponent;
});