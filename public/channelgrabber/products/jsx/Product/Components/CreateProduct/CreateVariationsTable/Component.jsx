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
        renderVariationsTableHeadings: function() {
            return (
                <tr>
                    <th className={'variations-table__th'}>Image</th>
                    <th className={'variations-table__th'}>SKU</th>
                    <th className={'variations-table__th'}>Quantity</th>
                    <th className={'variations-table__th'}>Stock Mode</th>
                    <th className={'variations-table__th'}>Attribute 1</th>
                    <th className={'variations-table__th'}>Attribute 2</th>
                    <th className={'variations-table__th'}>
                        <button type="button">add column</button>
                    </th>
                </tr>
            );
        },
        renderVariations: function() {
            var variations = this.props.variationsTable.variations;
            console.log('in render variations with variations: ', variations);
            return (
                variations.map(this.renderVariationRow, this)
            );
        },

        renderVariationRowField: function(variationId, field) {
            console.log('in renderVariationRowField with variationId: ', variationId, ' and field: ', field)
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

        renderVariationRow: function(variation) {
            var variationId = variation.id;
            return (
                <FormSection name={"variation-" + variationId}>
                    <tr>
                        {
                            this.props.variationsTable.fields.map(function(field){
                                return this.renderVariationRowField(variationId,field);
                            }.bind(this))
                        }
                    </tr>
                </FormSection>
            );
        },
        renderVariationsTable: function(variations) {
            return (
                <FormSection name={"variations"}>
                    <table className={'variations-table'}>
                        {this.renderVariationsTableHeadings()}
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

})
;
