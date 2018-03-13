define([
    'react',
    'redux-form'
], function(
    React,
    ReduxForm
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
                    <button type="submit">Submit</button>
                </form>
            );
        }
    });

    return ContactFormComponent;
});