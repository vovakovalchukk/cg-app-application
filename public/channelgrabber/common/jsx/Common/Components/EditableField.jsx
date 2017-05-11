define([
    'react',
    'Common/Components/ClickOutside'
], function(
    React,
    ClickOutside
) {
    "use strict";

    var EditableFieldComponent = React.createClass({
        getDefaultProps: function () {
            return {
                initialFieldText: ""
            };
        },
        getInitialState: function () {
            return {
                fieldText: this.props.initialFieldText,
                oldFieldText: this.props.initialFieldText,
                editable: false
            }
        },
        componentWillReceiveProps: function (newProps) {
            this.setState({
                fieldText: newProps.initialFieldText,
                oldFieldText: newProps.initialFieldText,
                editable: false
            });
        },
        onClick: function () {
            var editable = this.refs.input.focus ? true : !this.state.editable;
            this.setState({
                editable: editable
            });
        },
        onCancelInput: function () {
            this.setState({
                editable: false,
                fieldText: this.state.oldFieldText
            });
        },
        onKeyPress: function (e) {
            if (e.key === 'Enter') {
                this.onSubmitInput();
            }
        },
        onSubmitInput: function () {
            if (! this.state.editable) {
                return;
            }

            var promise = this.props.onSubmit(this.state.fieldText);
            promise.then(function(data) {
                this.setState({
                    editable: false,
                    fieldText: data.newFieldText,
                    oldFieldText: data.newFieldText
                });
            }.bind(this));
            promise.catch(function(error) {
                console.log(error.message);
            });
        },
        render: function () {
            return (
                <ClickOutside className="editable-field-wrap" onClickOutside={this.onCancelInput}>
                    <input
                        ref="input"
                        title="Click to edit"
                        className={"editable-field " + (this.state.editable ? "active" : "")}
                        value={this.state.fieldText}
                        onKeyPress={this.onKeyPress}
                        onClick={this.onClick}
                        onChange={function(e){this.setState({fieldText:e.target.value});}.bind(this)}
                        onFocus={function(){this.refs.input.select()}.bind(this)}
                    />
                    <div className="submit-input">
                        <div className={"submit-cancel " + (this.state.editable ? "active" : "")}>
                            <div className="button-input" onClick={this.onSubmitInput}><span className="submit"></span></div>
                            <div className="button-input" onClick={this.onCancelInput}><span className="cancel"></span></div>
                        </div>
                    </div>
                </ClickOutside>
            );
        }
    });

    return EditableFieldComponent;
});
