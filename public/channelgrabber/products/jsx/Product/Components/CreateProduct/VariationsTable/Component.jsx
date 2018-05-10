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

    var VariationsTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                newVariationRowRequest: null,
                variationValues: null
            };
        },
        shouldCreateNewVariationRow: function(variationId) {
            console.log('in shouldCreate.... with variationId : ',variationId);
            var variations = this.props.variationValues;
            if (!variations) {
                console.log('no variations');
                return true;
            }
            var variationValues = variations['variation-' + variationId.toString()];
            if (!variationValues) {
                console.log('no variation values');
                return true;
            } else {
                var nonDimensionalFieldNames = getNonDimensionalVariationFields(variationValues, this.props.variationsTable.fields);
                if (nonDimensionalFieldNames.length == 0) {
                    console.log('non.dimensionalfieldnames.length == variation values');
                    return true;
                }
            }
            return false;
        },
        getNewVariationId: function() {
            return (this.props.variationsTable.variations[this.props.variationsTable.variations.length - 1].id)
        },
        variationRowFieldOnChange: function(event, variationId) {
            console.log('in variationROwFieldOnChange....')
            if (this.shouldCreateNewVariationRow(variationId)) {
                console.log('should create new row...');
                this.props.newVariationRowCreate();
                this.props.setNewVariationDimensions(this.getNewVariationId());
            }
        },
        unsetAttributeFieldOnAllVariations: function(field){
          console.log('in unsetAttributeFieldOnAllVariations with field: ' , field, ' this.props : ' , this.props);
          var variationValues = this.props.variationValues;
          console.log('variationValues: ' , variationValues);
          for(var variation in variationValues){

              if(variation.indexOf('variation-') < 0 ){
                  continue;
              }

              console.log(variationValues[variation]);
          }

        },
        attributeColumnRemove: function(field){
            console.log('in attributeColumnRemove with field: ' , field , ' this.props: ' , this.props);
            var formSectionPrefix = 'variations.c-table-with-inputs__headings.';

            this.props.unregister(formSectionPrefix+field.name);
            this.props.change(formSectionPrefix+field.name, null);
            this.props.untouch(formSectionPrefix+field.name);

            // unregister , change and untouch all values of custom attribute for the variation rows
            this.unsetAttributeFieldOnAllVariations(field);

            this.props.attributeColumnRemove(field.name);
        },
        variationRowRemove: function(variationId, event) {
            console.log('in variaitonRowRemove with this.props:  ' , this.props);
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
                                        this.attributeColumnRemove.bind(this,field)
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
                    if (index == (variations.length - 1)) {
                        var isLastVariation = true;
                    } else {
                        var isLastVariation = false;
                    }
                    return this.renderVariationRow.call(this, variation, isLastVariation);
                }, this)
            );
        },
        renderVariationRow: function(variation, isLastVariation) {
            var variationId = variation.id;
            var removeVariationCellStyle = {
                background: 'white',
                border: 'none'
            };
            if (isLastVariation) {
                removeVariationCellStyle.display = 'none';
            }
            return (
                <FormSection name={"variation-" + variationId}>
                    <tr className={"u-border-none"}>
                        {this.renderVariationRowFields(variationId)}
                        <td style={removeVariationCellStyle} className={'c-table-with-inputs__cell'}>
                            <span
                                className={'c-icon-button c-icon-button--remove'}
                                onClick={this.variationRowRemove.bind(this, variationId)}
                            >
                                <i
                                    aria-hidden="true"
                                    className={'fa fa-2x fa-minus-square'}
                                />
                                <span className="u-margin-left-small">
                                    remove variation
                                </span>
                            </span>
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
