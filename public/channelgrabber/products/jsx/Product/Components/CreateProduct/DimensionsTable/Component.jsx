import React from 'react';
import {Field, FormSection} from 'redux-form';
import utility from 'Product/Components/CreateProduct/functions/utility';
import ImageDropDown from 'Common/Components/ImageDropDown';
import Select from 'Common/Components/Select';

class DimensionsTableComponent extends React.Component {
    static defaultProps = {
        fields: [],
        rows: [{}],
        values: [],
        formName: null,
        formSectionName: null,
        fieldChange: null,
        legend: null,
        massUnit: null,
        lengthUnit: null
    };

    renderHeadings = () => {
        var orderedFields = orderFields(this.props.fields);
        return orderedFields.map(function(field) {
            return this.renderHeading(field);
        }.bind(this));
    };

    renderHeading = (field) => {
        let label = field.label;
        if (field.isDimensionsField) {
            let units = (field.name == 'weight' ? this.props.massUnit : this.props.lengthUnit);
            label += ' (' + units + ')';
        }
        return (
            <th className={'c-table-with-inputs__cell c-table-with-inputs__cell-heading '}>{label}</th>
        );
    };

    renderHeaderRow = () => {
        return (
            <FormSection name={this.props.formSectionName["headerRow"]}>
                <tr>
                    {this.renderHeadings()}
                </tr>
            </FormSection>
        );
    };

    renderImageDropdown = (reduxFieldProps, variationId, uploadedImages) => {
        return <ImageDropDown
            selected={utility.getUploadedImageById(reduxFieldProps.input.value, uploadedImages)}
            autoSelectFirst={false}
            images={uploadedImages}
        />
    };

    renderCustomSelect = (field, reduxFormFieldProps) => {
        return <Select
            options={field.options}
            autoSelectFirst={false}
            title={name}
            customOptions={true}
            selectedOption={{
                name: reduxFormFieldProps.input.value,
                value: reduxFormFieldProps.input.value
            }}
        />
    };

    isFirstVariationRow = (variationId, field) => {
        // As the first row can't be deleted this should be safe
        return (variationId === 0);
    };

    changeAllOtherUnchangedValuesToMatchField = (field, targetValue) => {
        var variations = this.props.values.variations;
        for (var variation in variations) {
            var id = parseInt(variation.replace('variation-', ''));
            var variationFieldIdentifier = 'variations.' + variation + '.' + field.name;
            if (!this.cellHasChanged(id, field.id)) {
                this.props.fieldChange(
                    variationFieldIdentifier,
                    targetValue
                )
            }
        }
    };

    fieldOnChangeHandler = (variationId, field, event) => {
        this.props.cellChangeRecord(variationId, field.id);
        if (this.isFirstVariationRow(variationId, field)) {
            var value = event.target.value;
            this.changeAllOtherUnchangedValuesToMatchField(field, value);
        }
    };

    fieldNoInputRenderMethod = (type) => {
        let methods = {
            text: function (variationId, field, value) {
                return (
                    <span>{value}</span>
                )
            },
            image: function (variationId, field, imageId) {
                var uploadedImages = this.props.uploadedImages.images;
                var imageUrl = '';
                for (var i = 0; i < uploadedImages.length; i++) {
                    if (uploadedImages[i].id == imageId) {
                        imageUrl = uploadedImages[i].url;
                        break;
                    }
                }
                return (
                    <div className="image-dropdown-target">
                        <div className="react-image-picker">
                    <span className="react-image-picker-image">
                        <img src={imageUrl}/>
                    </span>
                        </div>
                    </div>

                );
            },
            customOptionsSelect: function (variationId, field, value) {
                return <span>{value}</span>;
            }
        };
        return methods[type];
    };

    fieldInputRenderMethod = (type) => {
        let methods = {
            text: function (variationId, field) {
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'c-table-with-inputs__text-input'}
                        component="input"
                        onChange={this.fieldOnChangeHandler.bind(this, variationId, field)}
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
                        onChange={this.fieldOnChangeHandler.bind(this, variationId, field)}
                    />
                )
            },
            image: function (variationId, field) {
                var uploadedImages = this.props.uploadedImages.images;
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component={function (props) {
                            return this.renderImageDropdown.call(this,
                                props,
                                variationId,
                                uploadedImages)
                        }.bind(this)}
                    />
                )
            },
            customOptionsSelect: function (variationId, field) {
                return <Field
                    name={field.name}
                    component={this.renderCustomSelect.bind(this, field)}
                />;
            }
        };
        return methods[type];
    };

    getFieldValueFromState = (variationId, field) => {
        var variations = this.props.values.variations;
        if (!variations) {
            return null;
        }
        var variationSelector = 'variation-' + variationId.toString();
        if (!variations[variationSelector]) {
            return null;
        }
        if (variations[variationSelector][field.name]) {
            return variations[variationSelector][field.name];
        }
    };

    cellHasChanged = (variationId, fieldId) => {
        var cells = this.props.cells;
        for (var i = 0; i < cells.length; i++) {
            if ((cells[i].variationId == variationId) && (cells[i].fieldId == fieldId) && cells[i].hasChanged) {
                return true;
            }
        }
        return false;
    };

    renderField = (variationId, field) => {
        var renderFieldMethod = null;
        var fieldValue = ''
        if (field.isDimensionsField) {
            renderFieldMethod = this.fieldInputRenderMethod(field.type).bind(this, variationId, field, fieldValue);
        } else {
            fieldValue = this.getFieldValueFromState(variationId, field);
            renderFieldMethod = this.fieldNoInputRenderMethod(field.type).bind(this, variationId, field, fieldValue);
        }
        return (
            <td className={'create-variations-table__td'}>
                {renderFieldMethod()}
            </td>
        )
    };

    renderRow = (variationId) => {
        var fields = this.props.fields;
        var orderedFields = orderFields(fields);
        return (
            <FormSection name={"variation-" + variationId}>
                <tr>
                    {orderedFields.map(function(field) {
                        return this.renderField(variationId, field);
                    }.bind(this))}
                </tr>
            </FormSection>
        )
    };

    renderRows = (isEmpty) => {
        var rows = this.props.rows;
        var rowsToRender = [];

        let loopLength = isEmpty ? 2 : rows.length;

        for (var i = 0; i < loopLength - 1; i++) {
            rowsToRender.push(this.renderRow(rows[i].id));
        }
        return rowsToRender;
    };

    renderTable = () => {
        let isEmpty = this.props.rows.length <= 1;
        return (
            <FormSection name={"variations"}>
                {this.props.legend ? <legend className={'u-heading-text'}>{this.props.legend}</legend> : ''}
                <table className={'c-table-with-inputs u-width-inherit u-min-width-50'}>
                    {this.renderHeaderRow()}
                    {this.renderRows(isEmpty)}
                </table>
            </FormSection>
        );
    };

    render() {
        return this.renderTable();
    }
}

export default DimensionsTableComponent;

function orderFields(fields) {
    var sortedFields = sortCustomAttributesToBeAfterSKUField(fields);
    return sortedFields;
}
function sortCustomAttributesToBeAfterSKUField(fields) {
    var customAttributeFields = [];
    var nonAttributeFields = [];
    for (var field in fields) {
        if (fields[field].isCustomAttribute) {
            customAttributeFields.push(fields[field]);
        } else {
            nonAttributeFields.push(fields[field]);
        }
    }
    var orderedFields = mergeArrayIntoArrayAtSpecifiedPosition(nonAttributeFields, customAttributeFields, 2);
    return orderedFields;
}
function mergeArrayIntoArrayAtSpecifiedPosition(array1, array2, position) {
    Array.prototype.splice.apply(array1, [position, 0].concat(array2));
    return array1
}

