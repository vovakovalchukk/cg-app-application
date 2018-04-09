define([
    'react',
    'redux-form'
], function (
    React,
    reduxForm
) {

    var Field = reduxForm.Field;
    var Form = reduxForm.Form;

    var createFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null
            };
        },

        render:function(){
            return (
                    <Form onSubmit={this.props.handleSubmit}>
                        <label>Title</label>
                        <div>
                            <Field type="text" name="title" component="input" />
                        </div>
                        <button type="submit" >Submit Button</button>
                    </Form>
            );
        }
    })


    return reduxForm.reduxForm({
        form:'createProductForm'
    })(createFormComponent);

});
