import React from 'react';
import {Form, Field, Fields, FormSection} from 'redux-form';
import utility from 'Product/Components/CreateProduct/functions/utility';
import stateFilters from 'Product/Components/CreateProduct/functions/stateFilters';
import InputWithValidation from 'Common/Components/ReduxForm/InputWithValidation';
import ImageDropDown from 'Common/Components/ImageDropDown';
import Select from 'Common/Components/Select';
import StockModeInputs from 'Product/Components/CreateProduct/StockModeInputsRoot';

var firstColumnCellStyle = {
    width: '2rem',
    minWidth: '0px',
    textAlign: 'center'
};

class VariationsTableComponent extends React.Component {
    static defaultProps = {
        newVariationRowRequest: null,
        variationValues: null,
        variationsTable: {}
    };

    shouldCreateNewVariationRow = (variationId) => {
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
    };

    getNewVariationId = () => {
        return (this.props.variationsTable.variations[this.props.variationsTable.variations.length - 1].id)
    };

    variationRowFieldOnChange = (variationId) => {
        if (this.shouldCreateNewVariationRow(variationId)) {
            this.props.newVariationRowCreate();
            this.props.setDefaultValuesForNewVariations(this.getNewVariationId());
        }
    };

    attributeColumnNameChange = (fieldName, value) => {
        this.props.attributeColumnNameChange(fieldName, value);
        if (attributeColumnHasNoValue(this.props.attributeValues, fieldName)) {
            this.props.newAttributeColumnRequest();
        }
    };

    variationRowRemove = (variationId) => {
        this.props.resetSection('variations.' + 'variation-' + variationId.toString());
        this.props.variationRowRemove(variationId);
    };

    renderVariationTableHeading = (field) => {
        if (field.isCustomAttribute) {
            var isLastAttributeFieldColumn = stateFilters.isLastAttributeFieldColumn(field, this.props.variationsTable);
            let fieldName = 'attributes.' + field.name;

            var renderRemoveButton = () => {
                return (
                    <button type="button"
                            className={'c-table-with-inputs__remove-button'}
                            onClick={this.props.attributeColumnRemove.bind(this, field.name)}
                    >
                        ❌
                    </button>
                );
            };

            return (
                <th className={'c-table-with-inputs__cell c-table-with-inputs__cell-heading'}>
                    <div>
                        <Field
                            type="text"
                            name={fieldName}
                            placeholder="Variation name (e.g. Color)"
                            className={"c-table-with-inputs__text-input"}
                            component="input"
                            onChange={(function(event) {
                                this.attributeColumnNameChange(field.name, event.target.value)
                            }.bind(this))}
                        />
                        {!isLastAttributeFieldColumn ? renderRemoveButton() : ''}
                    </div>
                </th>
            )
        } else {
            return (
                <th className={'c-table-with-inputs__cell c-table-with-inputs__cell-heading'}>
                    <div>
                        {field.label}
                    </div>
                </th>
            );
        }
    };

    renderVariationHeadings = () => {
        return this.props.variationsTable.fields.map(function(field) {
            return this.renderVariationTableHeading(field);
        }.bind(this));
    };

    renderVariationsTableHeaderRow = () => {
        return (
            <tr className={"c-table-with-inputs__header-row"}>
                <th style={firstColumnCellStyle}></th>
                {this.renderVariationHeadings()}
            </tr>
        );
    };

    renderImageDropdown = (reduxFieldProps, variationId, uploadedImages) => {
        return <ImageDropDown
            selected={utility.getUploadedImageById(reduxFieldProps.input.value, uploadedImages)}
            onChange={function(event) {
                reduxFieldProps.input.onChange(event.target.value)
            }}
            autoSelectFirst={false}
            images={uploadedImages}
        />
    };

    renderCustomSelect = (field, reduxFormFieldProps) => {
        var isLastAttributeFieldColumn = field.isCustomAttribute && stateFilters.isLastAttributeFieldColumn(field, this.props.variationsTable);
        return <Select
            disabled={isLastAttributeFieldColumn}
            options={field.options}
            autoSelectFirst={false}
            fullWidth={true}
            title={name}
            customOptions={true}
            customOptionsPlaceholder={field.isCustomAttribute ? "Add Attribute (e.g. Blue)" : null}
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
    };

    variationRowFieldInputRenderMethod = (type) => {
        let methods = {
            text: function (variationId, field) {
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'c-table-with-inputs__text-input'}
                        onChange={this.variationRowFieldOnChange.bind(this, variationId, field.id)}
                        component={InputWithValidation}
                    />
                )
            },
            number: function (variationId, field) {
                return (
                    <Field
                        type="number"
                        name={field.name}
                        className={'c-table-with-inputs__text-input'}
                        component="input"
                        onChange={this.variationRowFieldOnChange.bind(this, variationId, field.id)}
                    />
                )
            },
            image: function (variationId, field) {
                var uploadedImages = this.props.uploadedImages.images;
                return (
                    <Field
                        type="text"
                        name={field.name}
                        component={function (props) {
                            return this.renderImageDropdown.call(this,
                                props,
                                variationId,
                                uploadedImages)
                        }.bind(this)}
                        onChange={this.variationRowFieldOnChange.bind(this, variationId)}
                    />
                )
            },
            stockModeOptions: function (variationId) {
                return (
                    <Fields
                        type="text"
                        names={[
                            'stockModeType',
                            'stockAmount'
                        ]}
                        component={StockModeInputs}
                        onChange={this.variationRowFieldOnChange.bind(this, variationId)}
                    />
                );
            },
            customOptionsSelect: function (variationId, field) {
                return <Field
                    name={field.name}
                    component={this.renderCustomSelect.bind(this, field)}
                    onChange={this.variationRowFieldOnChange.bind(this, variationId)}
                />;
            }
        };
        return methods[type];
    };

    renderVariationRowField = (variationId, field) => {
        var renderFieldMethod = this.variationRowFieldInputRenderMethod(field.type).bind(this, variationId, field);
        return (
            <td className={'c-table-with-inputs__cell'}>
                {renderFieldMethod()}
            </td>
        )
    };

    renderVariationRowFields = (variationId) => {
        return this.props.variationsTable.fields.map(function(field) {
            return this.renderVariationRowField(variationId, field);
        }.bind(this))
    };

    renderVariations = () => {
        var variations = this.props.variationsTable.variations;
        return (
            variations.map(function(variation, index) {
                var isLastVariation = false;
                var isFirstVariation = false;
                if (index == (variations.length - 1)) {
                    isLastVariation = true;
                }
                if (index == 0) {
                    isFirstVariation = true;
                }
                return this.renderVariationRow.call(this, variation, isLastVariation, isFirstVariation);
            }, this)
        );
    };

    renderVariationRow = (variation, isLastVariation, isFirstVariation) => {
        var variationId = variation.id;
        var removeButtonStyle = {};
        var removeButtonStyles = [];
        if (isLastVariation) {
            removeButtonStyles.push('c-icon-button--remove__disabled')
        }
        if (isFirstVariation) {
            removeButtonStyles.push('u-display-none')
        }
        var removeOnClick = isLastVariation ? function() {
        } : this.variationRowRemove.bind(this, variationId);
        return (
            <FormSection name={"variations.variation-" + variationId}>
                <tr className={
                    "u-border-none " +
                    "c-table-with-inputs__row " +
                    (isLastVariation && !isFirstVariation ? "c-table-with-inputs__row--last " : '')
                }>
                    <td className={'c-table-with-inputs__cell'} style={firstColumnCellStyle}>
                        <span
                            className={'c-icon-button c-icon-button--remove u-inline-block u-float-none ' + removeButtonStyles.join(' ')}
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
    };

    renderVariationsTable = () => {
        return (
            <Form>
                <table className={'c-table-with-inputs c-table-with-inputs--extendable'}>
                    {this.renderVariationsTableHeaderRow()}
                    {this.renderVariations()}
                </table>
            </Form>
        );
    };

    render() {
        return this.renderVariationsTable();
    }
}

export default VariationsTableComponent;

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
function attributeColumnHasNoValue(attributeValues, fieldName) {
    return (!attributeValues || attributeValues[fieldName] == undefined);
}

