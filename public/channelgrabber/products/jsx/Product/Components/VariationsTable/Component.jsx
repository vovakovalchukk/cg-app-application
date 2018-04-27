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
    var Fields = reduxForm.Fields;
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
                    <th className={'' +
                    'c-table-with-inputs__cell ' +
                    'c-table-with-inputs__cell-heading '
                    }>
                        <Field
                            type="text"
                            name={field.name}
                            className={"c-table-with-inputs__text-input"}
                            component="input"
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
                selected={getUploadedImageById(reduxFieldProps.input.value, uploadedImages)}
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
                    console.log("in onOption change with option: ", option, ' and field.options ', field.options, ' and props : ', this.props);
                    if (!optionExistsAlready(option, field.options)) {
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
                    <table className={'c-table-with-inputs'}>
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

    function optionExistsAlready(option, options) {
        for (var i = 0; i < options.length; i++) {
            if (option.value == options[i].value) {
                console.log('option exists already');
                return true;
            }
        }
    }

    function getUploadedImageById(id, uploadedImages) {
        for (var i = 0; i < uploadedImages.length; i++) {
            if (uploadedImages[i].id == id) {
                return uploadedImages[i];
            }
        }
    }

});
