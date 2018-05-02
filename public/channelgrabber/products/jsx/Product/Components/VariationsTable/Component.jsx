define([
    'react',
    'redux-form',
    'Product/Components/CreateProduct/functions/utility',
    'Common/Components/ImageDropDown',
    'Common/Components/Select',
    'Product/Components/CreateProduct/StockModeInputs/Root'

], function(
    React,
    reduxForm,
    utility,
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
                variationValues:null
            };
        },
        variationRowFieldOnChange: function(event, variationId, fieldId) {
            var variationValues = this.props.variationValues['variation-'+variationId.toString()];
console.log("in vairationRowFieldOnChange variationValues: " , variationValues);

            if (!variationValues) {
                this.props.newVariationRowCreate();
            } else {
                var nonDimensionalFieldNames = getNonDimensionalVariationFields(variationValues, this.props.variationsTable.fields);

                console.log('nonDimensionalFieldNames: ' , nonDimensionalFieldNames, ' its .length : ' , nonDimensionalFieldNames.length);

                if (nonDimensionalFieldNames.length==0) {
                    this.props.newVariationRowCreate();
                }
            }




//            var currState = getState();
//            var variationValues = getVariationValues(currState, variationId);
//            if (!variationValues) {
//                dispatch(ActionCreators.newVariationRowCreate());
//            } else {
//                var nonDimensionalValues = getNonDimensionalVariationFields(variationValues,currState.variationsTable.fields);
//                if (nonDimensionalValues.length==0) {
//                    dispatch(ActionCreators.newVariationRowCreate());
//                }
//            }


        },
        variationRowRemove:function(variationId,event){
            console.log('in variationRowRemove with this.props: ', this.props , ' and variationId: ' , variationId ,' and event ' , event);
            this.props.resetSection('variations.'+'variation-'+variationId.toString());
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
        renderVariations: function() {
            var variations = this.props.variationsTable.variations;
            return (
                variations.map(this.renderVariationRow, this)
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
            stockModeOptions: function(variationId, field) {
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
            var removeVariationCellStyle = {
                background: 'white',
                border: 'none'
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
        renderVariationsTable: function(variations) {
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
            console.log("var field: " , field , ' in values: ' , values)
            if (isNonDimensionField(field, fields)) {
                fieldsToReturn.push(field)
            }
        }
        console.log('returning fields: ' , fieldsToReturn);
        return fieldsToReturn;
    }

    function isNonDimensionField(field, fields) {
            console.log('in isDimensionsField with field: ', field , ' and fields: ', fields);
        for (var i = 0; i < fields.length; i++) {
            if (fields[i].name == field) {
                console.log("found field fields[i] = " , fields[i]);

                return true;
            }
        }
    }

});
