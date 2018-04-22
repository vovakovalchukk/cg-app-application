define([
    'react',
    'redux-form'
], function(
    React,
    reduxForm
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
                    <th className={'variations-table__th'}>
                        <Field
                            type="text"
                            name={field.name}
                            className={'form-row__input'}
                            component="input"
//                            onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}
                        />
                    </th>
                )
            } else {
                jsx = (
                    <th className={'variations-table__th'}>{field.label}</th>
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
                <FormSection name={"variations-table-headings"}>
                    <tr>
                        {this.renderVariationHeadings()}
                        <th className={'variations-table__th'}>
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
        renderVariationRowField: function(variationId, field) {
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
                    <table className={'variations-table'}>
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
