define([
    'react',
    'redux-form',
    'Product/Components/CreateProduct/functions/utility',
    'Product/Components/CreateProduct/functions/stateFilters',
    'Common/Components/ReduxForm/InputWithValidation',
    'Common/Components/ImageDropDown',
    'Common/Components/Select',
    'Common/Components/RemoveIcon',
    'Product/Components/CreateProduct/StockModeInputs/Root'
], function(
    React,
    reduxForm,
    utility,
    stateFilters,
    InputWithValidation,
    ImageDropDown,
    Select,
    RemoveIcon,
    StockModeInputs
) {
    var Form = reduxForm.Form;
    var Field = reduxForm.Field;
    var Fields = reduxForm.Fields;
    var FormSection = reduxForm.FormSection;

    var firstColumnCellStyle = {
        width:'2rem',
        minWidth:'0px',
        textAlign:'center'
    };

    var VariationsTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                newVariationRowRequest: null,
                variationValues: null
            };
        },
        shouldCreateNewVariationRow: function(variationId) {
            var variations = this.props.variationValues;
            if (!variations) {
                return true;
            }
            var variationValues = variations['variation-' + variationId.toString()];
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
                this.props.setDefaultValuesForNewVariations(this.getNewVariationId());
            }
        },
        resetFieldValueInReduxForm:function(fieldPath){
            this.props.unregister(fieldPath);
            this.props.change(fieldPath, null);
            this.props.untouch(fieldPath);
        },
        unsetAttributeFieldOnAllVariations: function(field) {
            var variationValues = this.props.variationValues;
            for (var variation in variationValues) {
                if (variation.indexOf('variation-') < 0) {
                    continue;
                }
                this.resetFieldValueInReduxForm('variations.'+variation+'.'+field.name)
            }
        },
        attributeColumnRemove: function(field) {
            this.resetFieldValueInReduxForm('variations.c-table-with-inputs__headings.'+field.name);
            this.unsetAttributeFieldOnAllVariations(field);
            this.props.attributeColumnRemove(field.name);
        },
        variationRowRemove: function(variationId) {
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
                        <div>
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
                                    onClick={
                                        this.attributeColumnRemove.bind(this, field)
                                    }>❌
                            </button>
                        </div>


                    </th>
                )
            } else {
                jsx = (
                    <th className={'' +
                    'c-table-with-inputs__cell ' +
                    'c-table-with-inputs__cell-heading '
                    }>
                        <div>

                            {field.label}
                        </div>
                    </th>
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
                <FormSection name={"c-table-with-inputs__headings"}>
                    <tr>
                        <th style={firstColumnCellStyle}></th>
                        {this.renderVariationHeadings()}
                        <th className={'' +
                        'c-table-with-inputs__cell ' +
                        'c-table-with-inputs__cell-heading ' +
                        ' u-background-none'}>
                            <span
                                className={'c-icon-button c-icon-button--add'}
                                onClick={this.props.newAttributeColumnRequest}
                            >
                                <i
                                    aria-hidden="true"
                                    className={'fa fa-2x fa-plus'}
                                />
                                <span className={'u-margin-left-small'}>
                                    add attribute
                                </span>
                            </span>
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
                fullWidth={true}
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
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId, field.id)}
                        component={InputWithValidation}
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
                <td className={'c-table-with-inputs__cell'}>
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
                    var isLastVariation = false;
                    var isFirstVariation = false;
                    if (index == (variations.length - 1)) {
                        isLastVariation = true;
                    }
                    if (index == 0){
                        isFirstVariation = true;
                    }
                    return this.renderVariationRow.call(this, variation, isLastVariation, isFirstVariation);
                }, this)
            );
        },
        renderVariationRow: function(variation, isLastVariation, isFirstVariation) {
            var variationId = variation.id;
            var removeButtonStyle = {};
            var removeButtonStyles = [];
            if (isLastVariation) {
                removeButtonStyles.push('c-icon-button--remove__disabled')
            }
            if(isFirstVariation){
                removeButtonStyles.push('u-display-none')
            }
            var removeOnClick = isLastVariation ? function(){} : this.variationRowRemove.bind(this, variationId);
            return (
                <FormSection name={"variation-" + variationId}>
                    <tr className={"u-border-none"}>
                        <td className={'c-table-with-inputs__cell'} style={firstColumnCellStyle}>
                            <span
                                className={'c-icon-button c-icon-button--remove u-inline-block u-float-none ' + removeButtonStyles.join(' ') }
                                onClick={removeOnClick}
                                disabled={isLastVariation}
                            >
                                <i
                                    aria-hidden="true"
                                    className={'fa fa-2x fa-minus-square'}
                                    style={removeButtonStyle}
                                />
                            </span>
                        </td>
                        {this.renderVariationRowFields(variationId)}
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
