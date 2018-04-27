define([
    'react',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderRoot',
    'Common/Components/ImagePicker',
    'Common/Components/FormRow',
    'Product/Components/VatView',
    'Product/Components/VariationsTable/Root'

], function(
    React,
    reduxForm,
    ImageUploader,
    ImagePicker,
    FormRow,
    VatView,
    CreateVariationsTable
) {

    var Field = reduxForm.Field;
    var Form = reduxForm.Form;
    var FormSection = reduxForm.FormSection;

    var inputColumnRenderMethods = {
        newProductName: function() {
            return (
                <Field type="text" name="title" className={'form-row__input'} component="input"/>
            )
        },
        mainImage: function() {
            var uploadedImages = this.props.uploadedImages.images;
            return (
                <div>
                    <Field name="mainImage" type="text" component={function(props) {
                        return (
                            <ImagePicker
                                images={
                                    uploadedImages
                                }
                                onImageSelected={props.input.onChange}
                                multiSelect={false}
                            />
                        );
                    }}/>
                    <ImageUploader className={'form-row__input'}/>
                </div>
            );
        },
        taxRates: function() {
            return (<Field name="taxRates" component={function(props) {
                    return <VatView
                        parentProduct={{
                            taxRates: this.props.taxRates
                        }}
                        fullView={true}
                        onVatChanged={props.input.onChange}
                        variationCount={0}
                    />
                }.bind(this)}/>
            );
        }

    };

    var createFormComponent = React.createClass({
        getDefaultProps: function() {
            return {
                handleSubmit: null,
                addImage: null,
                uploadedImages: {},
                taxRates: null,
                newVariationRowRequest: null
            };
        },

        render: function() {
            return (
                <Form id="create-product-form" className={"form-root margin-bottom--small"}
                      onKeyDown={(e)=>{console.log('in key down')}}
                >
                    <fieldset className={'form-root__fieldset margin-bottom--small'}>
                        <FormRow
                            label={'New Product Name'}
                            inputColumnContent={inputColumnRenderMethods.newProductName.call(this)}
                        />
                        <FormRow
                            label={'Main Image'}
                            inputColumnContent={inputColumnRenderMethods.mainImage.call(this)}
                        />
                    </fieldset>
                    <fieldset className={'form-root__fieldset margin-bottom--small'}>
                        <FormRow
                            label={'Tax Rates'}
                            inputColumnContent={inputColumnRenderMethods.taxRates.call(this)}
                        />
                    </fieldset>
                    <fieldset className={'margin-bottom--small'}>
                        <legend className={'form-root__legend'}>Variations</legend>

                        <CreateVariationsTable />

                    </fieldset>
                </Form>
            );
        }
    });

    return reduxForm.reduxForm({
        form: 'createProductForm'
    })(createFormComponent);

})
;
