define([
    'react',
    'redux-form'
], function(
    React,
    ReduxForm
) {
    "use strict";

    var CustomFieldsComponent = React.createClass({
        getDefaultProps: function() {
            return {
                fields: []
            }
        },
        renderInput: function(field) {
            return (
                <input
                    style={{width: "110px"}}
                    type={field.type}
                    name={field.input.name}
                    value={field.input.value}
                    placeholder={field.placeholder}
                    onFocus={field.input.onFocus}
                />
            );
        },
        render: function() {
            var Field = ReduxForm.Field;
            return (
                <div>
                {this.props.fields.map(function(field, index, fields) {
                    return (
                        <div style={{clear: "both"}}>
                            <Field
                                name={`${field}.value`}
                                component={this.renderInput}
                                type="text"
                                placeholder="Custom Field Value"
                            />
                            <Field
                                name={`${field}.name`}
                                component={this.renderInput}
                                type="text"
                                placeholder="Custom Field Name"
                                onFocus={function() {
                                    if (index == fields.length-1) {
                                        fields.push({name:"",value:""});
                                    }
                                }}
                            />
                        </div>
                    );
                }.bind(this))}
                </div>
            );
        }
    });

    return CustomFieldsComponent;
});