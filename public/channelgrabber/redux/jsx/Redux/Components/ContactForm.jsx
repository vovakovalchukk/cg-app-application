define([
    'react',
    'redux-form',
    'Common/Components/Select',
    'Redux/Components/CustomFields'
], function(
    React,
    ReduxForm,
    Select,
    CustomFields
) {
    "use strict";

    var ContactFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null
            };
        },
        renderQueryType: function(field) {
            return (
                <Select
                    options={[{name: "General", value: "general"}, {name: "Other", value: "other"}]}
                    onOptionChange={function(selectedOption) {
                        field.input.onChange(selectedOption.value);
                    }}
                />
            );
        },
        render: function() {
            var Field = ReduxForm.Field;
            var FieldArray = ReduxForm.FieldArray;
            return (
                <form onSubmit={this.props.handleSubmit}>
                    <div className="half">
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
                        <div>
                            <label htmlFor="queryType">Query Type</label>
                            <Field name="queryType" component={this.renderQueryType} />
                        </div>
                        <FieldArray name="customFields" component={CustomFields} />
                        <button type="submit">Submit</button>
                    </div>
                </form>
            );
        }
    });

    return ContactFormComponent;
});