define([
    'react',
    'redux-form',
    'Product/Components/CreateProduct/functions/utility',
    'Common/Components/ImageDropDown',
    'Common/Components/Select'

], function(
    React,
    reduxForm,
    utility,
    ImageDropDown,
    Select
) {

    var Field = reduxForm.Field;
    var FormSection = reduxForm.FormSection;

    var DimensionsTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                fields: [],
                rows: [{}],
                values: [],
                formName: null,
                formSectionName: null,
                fieldChange: null
            };
        },
        renderHeadings: function() {
            return this.props.fields.map(function(field) {
                return this.renderHeading(field);
            }.bind(this));
        },
        renderHeading: function(field) {
            var jsx = (
                <th className={'' +
                'c-table-wfunctionsith-inputs__cell ' +
                'c-table-with-inputs__cell-heading '
                }>{field.label}</th>
            );
            return jsx;
        },
        renderHeaderRow: function() {
            return (
                <FormSection name={this.props.formSectionName["headerRow"]}>
                    <tr>
                        {this.renderHeadings()}
                    </tr>
                </FormSection>
            );
        },
        renderImageDropdown: function(reduxFieldProps, variationId, uploadedImages) {
            return <ImageDropDown
                selected={utility.getUploadedImageById(reduxFieldProps.input.value, uploadedImages)}
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
            />
        },
        isFirstVariationRow: function(variationId, field) {
            var variations = this.props.values.variations;
            for (var firstVariation in variations) {
                break;
            }
            if (firstVariation == 'variation-' + variationId.toString()) {
                return true;
            }
        },
        changeAllOtherUnchangedValuesToMatchField: function(field, targetValue) {
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
        },
        fieldOnChangeHandler: function(variationId, field, event) {
            this.props.cellChangeRecord(variationId, field.id);
            if (this.isFirstVariationRow(variationId, field)) {
                var value = event.target.value;
                this.changeAllOtherUnchangedValuesToMatchField(field, value);
            }
        },
        fieldNoInputRenderMethods: {
            text: function(variationId, field, value) {
                return (
                    <span>{value}</span>
                )
            },
            image: function(variationId, field, imageId) {
                var uploadedImages = this.props.uploadedImages.images;
                var imageUrl = '';
                for (var i = 0; i < uploadedImages.length; i++) {
                    if (uploadedImages[i].id == imageId) {
                        imageUrl = uploadedImages[i].url;
                        break;
                    }
                }
                return (
                    <span>
                        <img
                            src={imageUrl}/>
                    </span>
                )
            },
            customOptionsSelect: function(variationId, field, value) {
                return <span>{value}</span>;
            }
        },
        fieldInputRenderMethods: {
            text: function(variationId, field) {
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component="input"
                        onChange={this.fieldOnChangeHandler.bind(this, variationId, field)}
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
                    />
                )
            },
            customOptionsSelect: function(variationId, field) {
                return <Field
                    name={field.name}
                    component={this.renderCustomSelect.bind(this, field)}
                />;
            }
        },
        getFieldValueFromState: function(variationId, field) {
            var variations = this.props.values.variations;
            var variationSelector = 'variation-' + variationId.toString();
            if (!variations[variationSelector]) {
                return null;
            }
            if (!variations[variationSelector][field.name]) {
                return null
            } else {
                return variations[variationSelector][field.name];
            }
        },
        cellHasChanged: function(variationId, fieldId, fieldName) {
            var cells = this.props.cells;
            for (var i = 0; i < cells.length; i++) {
                if ((cells[i].variationId == variationId) && (cells[i].fieldId == fieldId)) {
                    if (cells[i].hasChanged) {
                        return true;
                    }
                }
            }
            return false;
        },
        isFirstRowVariation(variationId) {
            if (!this.props.values || !this.props.values.variations) {
                return undefined;
            }
            var variations = this.props.values.variations;
            for (var firstVariation in variations) {
                break;
            }
            if (firstVariation == 'variation-' + variationId + toString()) {
                return true;
            }
            return false;
        },
        getFirstRowValue: function(variationId, fieldName) {
            if (!this.props.values || !this.props.values.variations) {
                return undefined;
            }
            var variations = this.props.values.variations;
            for (var firstVariation in variations) {
                break;
            }
            var firstVariationObject = variations[firstVariation];
            if (isEmpty(firstVariationObject)) {
                return undefined;
            }
            if (!firstVariationObject[fieldName]) {
                return undefined;
            }
            var value = firstVariationObject[fieldName];
            return value;
        },
        renderField: function(variationId, field) {
            var renderFieldMethod = null;
            var fieldValue = ''

            if (field.isDimensionsField) {
//                var hasNotChanged = !this.cellHasChanged(variationId, field.id, field.name)
//                if (hasNotChanged) {
//                    this.setAllNonFirstRowValuesToFieldValue(variationId,field);
//                }
                renderFieldMethod = this.fieldInputRenderMethods[field.type].bind(this, variationId, field, fieldValue);
            } else {
                fieldValue = this.getFieldValueFromState(variationId, field);
                renderFieldMethod = this.fieldNoInputRenderMethods[field.type].bind(this, variationId, field, fieldValue);
            }
            return (
                <td className={'create-variations-table__td'}>
                    {renderFieldMethod()}
                </td>
            )
        },
        renderRow: function(variationId) {
            var fields = this.props.fields;
            return (
                <FormSection name={"variation-" + variationId}>
                    <tr>
                        {fields.map(function(field) {
                            return this.renderField(variationId, field);
                        }.bind(this))}
                    </tr>
                </FormSection>
            )
        },
        renderRows: function() {
            var rows = this.props.rows;
            var rowsToRender = [];
            for (var i = 0; i < rows.length; i++) {
                rowsToRender.push(this.renderRow(rows[i].id));
            }
            return rowsToRender;
        },
        renderTable: function() {
            return (
                <FormSection name={"variations"}>
                    <table className={'c-table-with-inputs ' + this.props.classNames.join(' ')}>
                        {this.renderHeaderRow()}
                        {this.renderRows()}
                    </table>
                </FormSection>
            );
        },
        render: function() {
            return this.renderTable();
        }
    });

    return DimensionsTableComponent;

    function isEmpty(obj) {
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                return false;
            }
        }
        return true;
    }

});
