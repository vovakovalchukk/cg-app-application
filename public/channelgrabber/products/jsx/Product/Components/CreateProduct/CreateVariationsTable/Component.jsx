define([
    'react',
    'redux-form',
    'Common/Components/ImageDropDown'
], function(
    React,
    reduxForm,
    ImageDropDown
) {

    var Field = reduxForm.Field;
    var FormSection = reduxForm.FormSection;

    var CreateVariationsTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                newVariationRowRequest: null
            };
        },
        variationRowFieldOnChange: function(event, variationId) {
            this.props.newVariationRowCreateRequest(variationId);
        },
        renderVariationTableHeading: function(field) {
            var jsx = '';
            if (field.isCustomAttribute) {
                jsx = (
                    <th className={'create-variations-table__th'}>
                        <Field
                            type="text"
                            name={field.name}
                            className={'create-variations-table__input'}
                            component="input"
                        />
                        <button className={'create-variations-table__th__remove-button'}
                                onClick={this.props.attributeColumnRemove.bind(this, field.name)}>❌
                        </button>
                    </th>
                )
            } else {
                jsx = (
                    <th className={'create-variations-table__th'}>{field.label}</th>
                );
            }
            return jsx;
        },
        renderVariationHeadings: function() {
            return this.props.variationsTable.fields.map(function(field) {
                return this.renderVariationTableHeading(field);
            }.bind(this));
        },
        renderVariationsTableHeaderRow: function() {
            return (
                <FormSection name={"create-variations-table-headings"}>
                    <tr>
                        {this.renderVariationHeadings()}
                        <th className={'create-variations-table__th'}>
                            <button
                                type="button"
                                onClick={this.props.newAttributeColumnRequest}
                            >
                                add attribute
                            </button>
                        </th>
                    </tr>
                </FormSection>
            );
        },
        renderVariations: function() {
            var variations = this.props.variationsTable.variations;
            return (
                variations.map(this.renderVariationRow, this)
            );
        },
        variationRowFieldInputRenderMethods: {
            text: function(variationId, field) {
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component="input"
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                    />
                )
            },
            image: function(variationId, field) {
                var uploadedImages = this.props.uploadedImages.images;
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component={function(props) {
                            return <ImageDropDown
                                selected={ (function(){
                                     var imageFieldValue = getImageFieldValueFromStateUsingVariationId.call(this, variationId);
                                     if(imageFieldValue){
                                         var imageId = imageFieldValue;
                                         if(imageId) {
                                             var image = getUploadedImageById.call(this,imageId);
                                             return image;
                                         }

                                     }
                                    }.bind(this)())
                                }
                                onChange={function(event) {
                                    props.input.onChange(event.target.value)
                                }}
                                autoSelectFirst={false}
                                images={uploadedImages}
                            />
                        }.bind(this)}
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                    />
                )
            }
        },
        renderVariationRowField: function(variationId, field) {
            var renderFieldMethod = this.variationRowFieldInputRenderMethods[field.type].bind(this, variationId, field);
            return (
                <td>
                    {renderFieldMethod()}
                </td>
            )
        },
        renderVariationRowFields: function(variationId) {
            return this.props.variationsTable.fields.map(function(field) {
                return this.renderVariationRowField(variationId, field);
            }.bind(this))
        },
        renderVariationRow: function(variation) {
            var variationId = variation.id;
            return (
                <FormSection name={"variation-" + variationId}>
                    <tr>
                        {this.renderVariationRowFields(variationId)}
                    </tr>
                </FormSection>
            );
        },
        renderVariationsTable: function(variations) {
            return (
                <FormSection name={"variations"}>
                    <table className={'create-variations-table'}>
                        {this.renderVariationsTableHeaderRow()}
                        {this.renderVariations()}
                    </table>
                </FormSection>
            );
        },
        render: function() {
            return this.renderVariationsTable();
        }
    });

    return CreateVariationsTableComponent;

    function getImageFieldValueFromStateUsingVariationId(variationId) {
        var variationValues = this.props.formVariationValues;
        if(!variationValues){
            return null;
        }
        var variationToSearchIn = variationValues['variation-' + variationId];
        if(variationToSearchIn && variationToSearchIn.image){
            console.log('image id found');
            var imageFieldValue = variationToSearchIn.image;
            return imageFieldValue;
        }
        return null;
    }

    function getUploadedImageById(id){
        var uploadedImages = this.props.uploadedImages.images;
        var image=null;
        for(var i=0; i<uploadedImages.length; i++){
            if(uploadedImages[i].id == id){
                image = uploadedImages[i];
                break;
            }
        }
        console.log('image got : ' , image);
        return image;
    }

});
