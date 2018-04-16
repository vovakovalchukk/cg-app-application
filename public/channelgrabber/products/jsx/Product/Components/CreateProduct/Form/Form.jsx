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
                addImage: null,
                uploadedImages: {}
            };
        },

        render: function() {

            var uploadedImages = this.props.uploadedImages.images;

            return (
                <Form id="create-product-form" onSubmit={this.props.handleSubmit}>
                    <div className={"order-form half"}>
                        <label>
                            <span className={"inputbox-label"}>New Product Name:</span>
                            <div className={"order-inputbox-holder"}>
                                <Field type="text" name="title" component="input"/>
                            </div>
                        </label>


                        <ImagePicker
                            images={
                                uploadedImages
                            }
                            onImageSelected={this.props.onChange}
                        />
                        <label>
                            <span className={"inputbox-label"}>Main Image:</span>
                            <div className={"order-inputbox-holder"}>

                                {/*<Field name="productImage" component={function(props){*/}
                                {/*console.log('this.props: ',this.props)*/}
                                {/*return <ImagePicker*/}
                                {/*images={this.props.uploadedImages.images}*/}
                                {/*onImageSelected={props.onChange}*/}
                                {/*/>*/}
                                {/*}} />*/}

                                <div className={"order-inputbox-holder"}>


                                    <ImageUploader/>
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
