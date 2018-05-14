define([
    'react'
], function(
    React
) {
    "use strict";

    var FormRow = React.createClass({
        getDefaultProps: function() {
            return {
                label: '',
                inputColumnContent: '',
                size: 'medium'
            };
        },
        getInitialState: function() {
            return {
                selectedImages: []
            };
        },
        renderLabel: function() {
            if (!this.props.label) {
                return;
            }
            return (
                <label className={"form-row__label-column"}>{this.props.label}</label>
            );
        },
        render: function() {
            return (
                <div className={"c-form-row"}>
                    <label className={"c-form-row__label-column"}>{this.props.label}</label>
                    <div className={"c-form-row__input-column"}>
                        {this.props.inputColumnContent}
                    </div>
                </div>
            );
        }
    });

    return FormRow;
});


