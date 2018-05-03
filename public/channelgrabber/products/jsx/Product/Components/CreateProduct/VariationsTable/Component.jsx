define([
    'react',
    'redux-form',
    'Product/Components/CreateProduct/functions/utility',
    'Product/Components/CreateProduct/functions/stateFilters',
    'Common/Components/ImageDropDown',
    'Common/Components/Select',
    'Product/Components/CreateProduct/StockModeInputs/Root'
], function(
    React,
    reduxForm,
    utility,
    stateFilters,
    ImageDropDown,
    Select,
    StockModeInputs
) {
    var Form = reduxForm.Form;
    var Field = reduxForm.Field;
    var Fields = reduxForm.Fields;
    var FormSection = reduxForm.FormSection;

    var VariationsTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                newVariationRowRequest: null,
                variationValues: null
            };
        },
        shouldCreateNewVariationRow: function(variationId) {
            var variationValues = this.props.variationValues['variation-' + variationId.toString()];
            if (!variationValues) {
                return true;
            } else {
                var nonDimensionalFieldNames = getNonDimensionalVariationFields(variationValues, this.props.variationsTable.fields);
                if (nonDimensionalFieldNames.length == 0) {
                    return true;
                }
            }
            return false;
        },
        getNewVariationId: function() {
            return (this.props.variationsTable.variations[this.props.variationsTable.variations.length - 1].id)
        },
        variationRowFieldOnChange: function(event, variationId) {
            if (this.shouldCreateNewVariationRow(variationId)) {
                this.props.newVariationRowCreate();
                this.props.setNewVariationDimensions(this.getNewVariationId());
            }
        },
        variationRowRemove: function(variationId, event) {
            this.props.resetSection('variations.' + 'variation-' + variationId.toString());
            this.props.variationRowRemove(variationId);
        },
        renderVariationTableHeading: function(field) {
            var jsx = '';
            if (field.isCustomAttribute) {
                jsx = (
                    <th className={'' +
                    'c-table-with-inputs__cell ' +
                    'c-table-with-inputs__cell-heading '
                    }>
                        <Field
                            type="text"
                            name={field.name}
                            className={"c-table-with-inputs__text-input"}
                            component="input"
                            onChange={(function(event) {
                                this.props.attributeColumnNameChange(field.name, event.target.value)
                            }.bind(this))}
                        />
                        <button type="button" className={'c-table-with-inputs__remove-button'}
                                onClick={this.props.attributeColumnRemove.bind(this, field.name)}>❌
                        </button>
                    </th>
                )
            } else {
                jsx = (
                    <th className={'' +
                    'c-table-with-inputs__cell ' +
                    'c-table-with-inputs__cell-heading '
                    }>{field.label}</th>
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
                        <th className={'' +
                        'c-table-with-inputs__cell ' +
                        'c-table-with-inputs__cell-heading ' +
                        ' u-background-none'}>
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
        renderImageDropdown: function(reduxFieldProps, variationId, uploadedImages) {
            return <ImageDropDown
                selected={utility.getUploadedImageById(reduxFieldProps.input.value, uploadedImages)}
                onChange={function(event) {
                    reduxFieldProps.input.onChange(event.target.value)
                }}
                autoSelectFirst={false}
                images={uploadedImages}
            />
        },
        renderCustomSelect: function(field, reduxFormFieldProps) {
            return <Select
                options={field.options}
                autoSelectFirst={false}
                title={name}
                customOptions={true}
                selectedOption={{
                    name: reduxFormFieldProps.input.value,
                    value: reduxFormFieldProps.input.value
                }}
                onOptionChange={function(option) {
                    if (!utility.optionExistsAlready(option, field.options)) {
                        this.props.addNewOptionForAttribute(option, field.name);
                    }
                    return reduxFormFieldProps.input.onChange(option.value);
                }.bind(this)}
            />
        },
        variationRowFieldInputRenderMethods: {
            text: function(variationId, field) {
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component="input"
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId, field.id)}
                    />
                )
            },
            number: function(variationId, field) {
                return (
                    <Field
                        type="number"
                        name={field.name}
                        className={'form-row__input'}
                        component="input"
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId, field.id)}
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
                                props,
                                variationId,
                                uploadedImages)
                        }.bind(this)}
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                    />
                )
            },
            stockModeOptions: function(variationId) {
                return (
                    <Fields
                        type="text"
                        names={[
                            'stockModeType',
                            'stockAmount'
                        ]}
                        className={'form-row__input'}
                        component={StockModeInputs}
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                    />
                );
            },
            customOptionsSelect: function(variationId, field) {
                return <Field
                    name={field.name}
                    component={this.renderCustomSelect.bind(this, field)}
                    onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                />;
            }
        }
        ,
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
        renderVariations: function() {
            var variations = this.props.variationsTable.variations;
            return (
                variations.map(function(variation, index) {
                    if (index == (variations.length - 1)) {
                        var isLastVariation = true;
                    } else {
                        var isLastVariation = false;
                    }
                    return this.renderVariationRow.call(this,variation,isLastVariation);
                }, this)
            );
        },
        renderVariationRow: function(variation,isLastVariation) {
            var variationId = variation.id;
            var removeVariationCellStyle = {
                background: 'white',
                border: 'none'
            };
            if(isLastVariation){
                removeVariationCellStyle.display = 'none';
            }
            return (
                <FormSection name={"variation-" + variationId}>
                    <tr className={"u-border-none"}>
                        {this.renderVariationRowFields(variationId)}
                        <td style={removeVariationCellStyle}>
                            <button type="button" onClick={this.variationRowRemove.bind(this, variationId)}>remove
                            </button>
                        </td>
                    </tr>
                </FormSection>
            );
        },
        renderVariationsTable: function() {
            return (
                <Form>
                    <FormSection name={"variations"}>
                        <table className={'c-table-with-inputs'}>
                            {this.renderVariationsTableHeaderRow()}
                            {this.renderVariations()}
                        </table>
                    </FormSection>
                </Form>
            );
        },
        render: function() {
            return this.renderVariationsTable();
        }
    });

    return VariationsTableComponent;

    function getNonDimensionalVariationFields(values, fields) {
        var fieldsToReturn = [];
        for (var field in values) {
            if (isNonDimensionField(field, fields)) {
                fieldsToReturn.push(field)
            }
        }
        return fieldsToReturn;
    }
    function isNonDimensionField(field, fields) {
        for (var i = 0; i < fields.length; i++) {
            if (fields[i].name == field) {
                return true;
            }
        }
    }
});
