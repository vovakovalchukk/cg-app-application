define([
    'react',
    'redux-form',
    'Product/Components/CreateListing/Form/Shared/ImageDropDown'
], function(
    React,
    reduxForm,
    ImageDropDown
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
        variationRowFieldInputRenderMethods: {
            text: function(variationId, field) {
                return (
                    <td>
                        <Field
                            type="text"
                            name={field.name}
                            className={'form-row__input'}
                            component="input"
                            onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                        />
                    </td>
                )
            },
            image: function(variationId, field) {
                console.log('in image render method with props');
                var uploadedImages = this.props.uploadedImages.images;
                console.log('upladedImages: ', uploadedImages);
                return (
                    <Field
                        type="text"
                        name={field.name}
                        className={'form-row__input'}
                        component={function(props){
                            return <ImageDropDown
                                onChange={props.onChange}
                                images={uploadedImages}
                            />
                        }}
                        onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                    />
                )
            }
        },
        renderVariationRowField: function(variationId, field) {
            console.log('in renderVariationRowFIeld with field: ', field)
            var renderFieldMethod = this.variationRowFieldInputRenderMethods[field.type].bind(this , variationId, field);
            return (
                <td>
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

});
