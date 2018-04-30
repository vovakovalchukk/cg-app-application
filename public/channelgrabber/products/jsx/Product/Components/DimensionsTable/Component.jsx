define([
    'react',
    'redux-form',
    'Product/Components/CreateProduct/functions/utility',
    'Common/Components/ImageDropDown',
    'Common/Components/Select',

], function(
    React,
    reduxForm,
    utility,
    ImageDropDown,
    Select,
) {

    var Field = reduxForm.Field;
    var Fields = reduxForm.Fields;
    var FormSection = reduxForm.FormSection;

    var DimensionsTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                fields: [],
                rows: [{}],
                values: [],
                formSectionName: null
            };
        },
        renderHeadings: function() {

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
        variationRowFieldInputRenderMethods: {
            text: function(variationId, field) {
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component="input"
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
        renderField: function(variationId, field) {
            var renderFieldMethod = this.variationRowFieldInputRenderMethods[field.type].bind(this, variationId, field);
            return (
                <td className={'create-variations-table__td'}>
                    {renderFieldMethod()}
                </td>
            )
        },
        renderRow: function(variationId) {
            // loop over fields and assign value if one exists in values for it
            var fields = this.props.fields;
//
//            var fieldsToRender = []
//            for (var i = 0; i < fields.length; i++) {
//                fieldsToRender.push(this.renderField(variationId, fields[i]));
//            }

            return (
                <FormSection name={"variation-" + variationId}>
                    <tr>
                        {/*{fieldsToRender}*/}

                        {fields.map(function(field) {
                            console.log("mapping field render field: " , field)
                            return this.renderField(variationId, field);

                        }.bind(this))}

                    </tr>
                </FormSection>
            )

        },
        renderRows: function() {
            var variations = this.props.values;
            for (var variation in variations) {
                return this.renderRow(variation.id);
            }
        },
        renderTable: function() {
            return (
                <FormSection name={"variVariationations"}>
                    <table className={'c-table-with-inputs'}>
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

});
