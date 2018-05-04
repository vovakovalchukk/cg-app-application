define([
    'react',
    'redux-form',
    'react-redux',
    'Product/Components/CreateProduct/functions/stateFilters',
    'Common/Components/ImageUploader/ImageUploaderRoot',
    'Common/Components/ImagePicker',
    'Common/Components/FormRow',
    'Product/Components/VatView',
    'Product/Components/CreateProduct/VariationsTable/Root',
    'Product/Components/CreateProduct/DimensionsTable/Root'
], function(
    React,
    reduxForm,
    ReactRedux,
    stateFilters,
    ImageUploader,
    ImagePicker,
    FormRow,
    VatView,
    VariationsTable,
    DimensionsTable
) {
    var Field = reduxForm.Field;
    var Form = reduxForm.Form;

    var renderField = function(props) {
        var name = props.input.name;
        var onBlur = props.input.onBlur;
        var onFocus = props.input.onFocus;
        var onChange = props.input.onChange;
        var onDragStart = props.input.onDragStart;
        var onDrop = props.input.onDrop;
        var value = props.input.value;
        var type = props.type;
        return (<div>
            <div>
                <input
                    name={name}
                    onBlur={onBlur}
                    onFocus={onFocus}
                    onChange={onChange}
                    onDragStart={onDragStart}
                    onDrop={onDrop}
                    value={value}
                    type={type}
                    className={'form-row__input'}
                />
                <div className="u-color-red">
                    {props.meta.touched && props.meta.error}
                </div>
                {/*{props.touched &&*/}
                {/*((props.meta.error && <span>{props.meta.error}</span>) ||*/}
                {/*(props.warning && <span>{props.warning}</span>))}*/}
            </div>
        </div>);
    }

    var inputColumnRenderMethods = {
        newProductName: function() {
            return (
                <Field type="text" name="title" component={renderField}/>
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
                <Form id="create-product-form" className={"form-root margin-bottom--small"}>
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
                        <VariationsTable
                            resetSection={this.props.resetSection}
                        />
                        <DimensionsTable
                            stateSelectors={{
                                fields: ['variationsTable', 'fields'],
                                rows: ['variationsTable', 'variations'],
                                values: ['form', 'createProductForm', 'variations']
                            }}
                            stateFilters={{
                                fields: stateFilters.filterFields.bind(2)
                            }}
                            formName='createProductForm'
                            formSectionName='dimensionsTable'
                            classNames={['u-margin-top-small']}
                            fieldChange={this.props.change}
                        />
                    </fieldset>
                </Form>
            );
        }
    });

    return reduxForm.reduxForm({
        form: 'createProductForm',
        initialValues: {
            variations: {}
        },
        validate: validate
    })(createFormComponent);

    function validate(values) {
        const errors = {};
        console.log('in validation with values: ', values);
        if (!values.title) {
            console.log('no title!');
            errors.title = 'Required';
        }
        return errors;
    }

});
