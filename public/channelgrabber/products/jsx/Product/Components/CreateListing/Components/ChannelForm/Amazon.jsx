define([
    'react',
    'redux-form',
    'Common/Components/TextArea'
], function(
    React,
    ReduxForm,
    TextArea
) {
    "use strict";

    var Field = ReduxForm.Field;

    var AmazonChannelFormComponent = React.createClass({
        renderConditionNote: function(field) {
            return <label className="input-container">
                <span className={"inputbox-label"}>{field.displayTitle}</span>
                <div className={"order-inputbox-holder"}>
                    <TextArea
                        {...field.input}
                        className={"textarea-description"}
                    />
                </div>
            </label>;
        },
        render: function() {
            return (
                <div className="amazon-channel-form-container channel-form-container">
                    <Field name="conditionNote" component={this.renderConditionNote} displayTitle="Condition note"/>
                </div>
            );
        }
    });
    return AmazonChannelFormComponent;
});