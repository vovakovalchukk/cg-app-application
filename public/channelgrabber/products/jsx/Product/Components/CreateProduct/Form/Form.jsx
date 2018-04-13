define([
    'react',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderRoot',
    'Common/Components/ImagePicker'
], function(
    React,
    reduxForm,
    ImageUploader,
    ImagePicker
) {
    var Field = reduxForm.Field;
    var Form = reduxForm.Form;

    console.log('in connected Form');

    var createFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null,
                addImage:null
            };
        },
        render: function() {
            return (
                <Form id="create-product-form" onSubmit={this.props.handleSubmit}>
                    <div className={"order-form half"}>
                        <label>
                            <span className={"inputbox-label"}>New Product Name:</span>
                            <div className={"order-inputbox-holder"}>
                                <Field type="text" name="title" component="input"/>
                            </div>
                        </label>

                        <label>
                            <span className={"inputbox-label"}>Main Image:</span>
                            <div className={"order-inputbox-holder"}>
                                {/*<Field name="productImage" component={function(props){*/}
                                   {/*return <ImagePicker*/}
                                       {/*images={}*/}
                                       {/*onImageSelected={props.onChange}*/}
                                   {/*/>*/}
                                {/*}}/>*/}
                                <button onClick={this.props.addImage}>this button triggers add image</button>

                                <div className={"order-inputbox-holder"}>
                                    <ImageUploader
                                        onImageUploadStart={this.props.imageUploadStart}
                                        onImageUploadSuccess={this.props.imageUploadSuccess}
                                        onImageUploadFailure={this.props.imageUploadFailure}
                                    />
                                </div>

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
