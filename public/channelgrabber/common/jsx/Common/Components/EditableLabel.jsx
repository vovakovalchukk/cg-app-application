define([
    'react',
    'Common/Components/ClickOutside'
], function(
    React,
    ClickOutside
) {
    "use strict";

    var EditableLabelComponent = React.createClass({
        getDefaultProps: function () {
            return {
                initialLabelText: ""
            };
        },
        getInitialState: function () {
            return {
                labelText: this.props.initialLabelText,
                hasFocus: false
            }
        },
        onClick: function () {


        },
        render: function () {
            return (
                <ClickOutside className="editable-label-wrap" onClickOutside={function(){this.setState({hasFocus:false})}.bind(this)}>
                    <span className="editable-label" onClick={this.onClick}>{this.state.labelText}</span>
                </ClickOutside>
            );
        }
    });

    return EditableLabelComponent;
});
