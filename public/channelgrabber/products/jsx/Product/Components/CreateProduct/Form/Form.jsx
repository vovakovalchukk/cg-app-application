define([
    'react',
    'redux-form',
    'Common/Components/ImageUploader/ImageUploaderRoot',
    'Common/Components/ImagePicker',
    'Common/Components/FormRow',
    'Product/Components/VatView'
], function(
    React,
    reduxForm,
    ImageUploader,
    ImagePicker,
    FormRow,
    VatView
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
        getVariations: function() {
            return [
                {
                    image: 'image',
                    sku: 'unique-t-shirt',
                    quantity: 2,
                    stockMode: 'sdfsdfsdfsd',
                    attribute1: 'attribute1',
                    attribute2: 'attribute2'
                },
                {
                    sku: 'some new name here'

                }
            ];
        },
        variationRowFieldOnChange: function(event,variationId) {
            this.props.newVariationRowCreateRequest(variationId);
        },
        renderVariationsTableHeadings: function() {
            return (
                <tr>
                    <th className={'variations-table__th'}>Image</th>
                    <th className={'variations-table__th'}>SKU</th>
                    <th className={'variations-table__th'}>Quantity</th>
                    <th className={'variations-table__th'}>Stock Mode</th>
                    <th className={'variations-table__th'}>Attribute 1</th>
                    <th className={'variations-table__th'}>Attribute 2</th>
                    <th className={'variations-table__th'}>
                        <button type="button">add column</button>
                    </th>
                </tr>
            );
        },
        renderVariations: function() {
            var variations = this.props.variationRowProperties.variations;
            console.log('in render variations with variations: ', variations);

            variations.forEach(function(variation) {
                this.renderVariationRow.call(this, variation);
            }.bind(this));
            return (
                variations.map(this.renderVariationRow, this)
            );
        },
        renderBlankVariationRowField: function(placeholder) {
            return (
                <td><input type={'text'} className={'variations-table__input'} placeholder={placeholder}/></td>
            )
        },
        renderVariationRow: function(variation) {
            var variationId = variation.id;
            return (
                <FormSection name={"variation-" + variationId}>
                    <tr>

                        {/*<td><Field type="text" name={"image"} className={'form-row__input'} component="input" onChange={*/}
                        {/*function(event) {*/}

                        {/*this.props.newVariationRowCreateRequest(event,variationId);*/}

                        {/*}.bind(this)*/}
                        {/*}/></td>*/}
                        <td><Field type="text" name={"image"} className={'form-row__input'} component="input"
                                   onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}/></td>
                        <td><Field type="text" name={"sku"} className={'form-row__input'} component="input"/></td>
                        <td><Field type="text" name={"quantity"} className={'form-row__input'} component="input"/></td>
                        <td><Field type="text" name={"stockMode"} className={'form-row__input'} component="input"/></td>

                        {/*<td><Field type="text" name="variation-1-attr1" className={'form-row__input'} component="input"/></td>*/}
                        {/*<td><Field type="text" name="variation-1-attr2" className={'form-row__input'} component="input"/></td>*/}

                        {/*<td> <input type={'text'} className={'variations-table__input'} placeholder={'sku'}/> </td>*/}
                        {/*<td> <input type={'text'} className={'variations-table__input'} placeholder={'quantity'}/> </td>*/}
                        {/*<td> <input type={'text'} className={'variations-table__input'} placeholder={'stockMode'}/> </td>*/}
                        {/*<td> <input type={'text'} className={'variations-table__input'} placeholder={'attr1'}/> </td>*/}
                        {/*<td> <input type={'text'} className={'variations-table__input'} placeholder={'attr2'}/>  </td>*/}
                        {/*<td><button type="button">add variation</button></td>*/}
                    </tr>
                </FormSection>
            );
        },
        renderVariationsTable: function(variations) {
            return (
                <FormSection name={"variations"}>
                    <table className={'variations-table'}>
                        {this.renderVariationsTableHeadings()}
                        {this.renderVariations()}
                        {/*{this.renderBlankVariationRow()}*/}
                        {/*{this.renderVariationRow(0)}*/}

                    </table>
                </FormSection>
            );
        },
//        renderVariation: function(variation) {
//            return (
//                <tr>
//                    <td>{variation.image}</td>
//                    <td>{variation.sku}</td>
//                    <td>{variation.quantity}</td>
//                    <td>{variation.stockMode}</td>
//                    {/*<td>{variation.attribute1}</td>*/}
//                    {/*<td>{variation.attribute2}</td>*/}
//                    <td>
//                        <button type="button">remove</button>
//                    </td>
//                </tr>
//            );
//
//        },
        render: function() {
            return (
                <Form id="create-product-form" className={"form-root margin-bottom--small"}
                      onSubmit={this.props.handleSubmit}>
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

                        {this.renderVariationsTable(this.getVariations())}

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
