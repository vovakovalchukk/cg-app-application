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
                size:'medium'
            };
        },
        getInitialState: function() {
            return {
                selectedImages: []
            };
        },
        render: function() {
            return (
                <div className={"form-row form-row--"+this.props.size}>
                    <label className={"form-row__label-column"}>{this.props.label}</label>
                    <div className={"form-row__input-column"}>
                        {this.props.inputColumnContent}
                    </div>
                </div>
            );
        }
    });

    return FormRow;
});


