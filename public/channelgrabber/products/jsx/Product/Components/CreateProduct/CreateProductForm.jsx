define([
    'react',
    'redux-form',
    'Common/Components/ImageUploader'
], function(
    React,
    reduxForm,
    ImageUploader
) {
    var Field = reduxForm.Field;
    var Form = reduxForm.Form;

    console.log('ImageUploader in CreateProdctForm: ', ImageUploader);

    var createFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null
            };
        },
        render: function() {
            return (
                <Form id="create-product-form" onSubmit={this.props.handleSubmit}>
                    <div className={"order-form half"}>
                        <label>
                            <span className={"inputbox-label"}>Title:</span>
                            <div className={"order-inputbox-holder"}>
                                <Field type="text" name="title" component="input"/>
                            </div>
                        </label>
                        <label>
                            <span className={"inputbox-label"}>Image:</span>
                            <div className={"order-inputbox-holder"}>
                                <Field name="product-image" component={ImageUploader} />
                            </div>
                        </label>

                    </div>
                </Form>
            );
        }
    });

    return reduxForm.reduxForm({
        form: 'createProductForm'
    })(createFormComponent);
});
