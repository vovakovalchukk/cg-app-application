define([
    'react',
    'redux-form',
    'Common/Components/ImageDropDown',
    'Common/Components/Select',
    'Product/Components/CreateProduct/StockModeInputs/Root'

], function(
    React,
    reduxForm,
    ImageDropDown,
    Select,
    StockModeInputs
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
        renderImageDropdown: function(onChange, variationId, uploadedImages) {
            return <ImageDropDown
                selected={getSelectedImage.call(this, variationId)}
                onChange={function(event) {
                    onChange(event.target.value)
                }}
                autoSelectFirst={false}
                images={uploadedImages}
            />
        },
//        renderStockModeInputs: function(onChange, value) {
//            console.log('in renderStockModeInputs with onChange :', onChange);
//            return <StockModeInputs
//                //                options={options}
//                onChange={onChange}
//                selected={value}
//            />
//        },
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
                            return this.renderImageDropdown.call(this,
                                props.input.onChange,
                                variationId,
                                uploadedImages)
                        }.bind(this)}
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                    />
                )
            },
            stockModeOptions: function(variationId, field) {
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component={function(props) {
                            console.log("in component with props: ", props)

                            return <StockModeInputs
                                onChange={props.input.onChange}
                                onChange={function(event) {
                                    props.input.onChange(event.target.value)
                                }}
                                selected={props.input.onChange}
                            />

                        }.bind(this)}
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                    />
                )

            },
            customOptionsSelect: function(variationId, field) {
                console.log('in customOptionsSelect with variationId: ', variationId);
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component="input"
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                    />
                )
            }
        },
        renderVariationRowField: function(variationId, field) {
            var renderFieldMethod = this.variationRowFieldInputRenderMethods[field.type].bind(this, variationId, field);
            return (
                <td className={'create-variations-table__td'}>
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

    function getSelectedImage(variationId) {
        var imageFieldValue = getImageFieldValueFromStateUsingVariationId.call(this, variationId);
        if (imageFieldValue) {
            var imageId = imageFieldValue;
            if (imageId) {
                var image = getUploadedImageById.call(this, imageId);
                return image;
            }

        }
        return null;
    }

    function getImageFieldValueFromStateUsingVariationId(variationId) {
        var variationValues = this.props.formVariationValues;
        if (!variationValues) {
            return null;
        }
        var variationToSearchIn = variationValues['variation-' + variationId];
        if (variationToSearchIn && variationToSearchIn.image) {
            var imageFieldValue = variationToSearchIn.image;
            return imageFieldValue;
        }
        return null;
    }

    function getUploadedImageById(id) {
        var uploadedImages = this.props.uploadedImages.images;
        var image = null;
        for (var i = 0; i < uploadedImages.length; i++) {
            if (uploadedImages[i].id == id) {
                image = uploadedImages[i];
                break;
            }
        }
        return image;
    }

});
