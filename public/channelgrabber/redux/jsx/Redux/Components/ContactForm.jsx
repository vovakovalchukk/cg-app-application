define([
    'react',
    'redux-form',
    'Redux/Components/CustomFields'
], function(
    React,
    ReduxForm,
    CustomFields
) {
    "use strict";

    var ContactFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null
            };
        },
        render: function() {
            var Field = ReduxForm.Field;
            var FieldArray = ReduxForm.FieldArray;
            return (
                <form onSubmit={this.props.handleSubmit}>
                    <div>
                        <label htmlFor="firstName">First Name</label>
                        <Field name="firstName" component="input" type="text" />
                    </div>
                    <div>
                        <label htmlFor="lastName">Last Name</label>
                        <Field name="lastName" component="input" type="text" />
                    </div>
                    <div>
                        <label htmlFor="email">Email</label>
                        <Field name="email" component="input" type="email" />
                    </div>
                    <FieldArray name="customFields" component={CustomFields} />
                    <button type="submit">Submit</button>
                </form>
            );
        }
    });

    return ContactFormComponent;
});