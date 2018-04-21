define([
    'react',
    'redux-form',
], function(
    React,
    reduxForm,
) {

    var Field = reduxForm.Field;
    var FormSection = reduxForm.FormSection;

    var CreateVariationsTableComponent = React.createClass({
        getDefaultProps: function() {
            return {
                newVariationRowRequest: null
            };
        },
        variationRowFieldOnChange: function(event,variationId) {
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
            var variations = this.props.variationRowProperties.variations;
            console.log('in render variations with variations: ', variations);

            variations.forEach(function(variation) {
                this.renderVariationRow.call(this, variation);
            }.bind(this));
            return (
                variations.map(this.renderVariationRow, this)
            );
        },
        renderBlankVariationRowField: function(placeholder) {
            return (
                <td><input type={'text'} className={'variations-table__input'} placeholder={placeholder}/></td>
            )
        },
        renderVariationRow: function(variation) {
            var variationId = variation.id;
            return (
                <FormSection name={"variation-" + variationId}>
                    <tr>
                        <td><Field type="text" name={"image"} className={'form-row__input'} component="input"
                                   onChange={this.variationRowFieldOnChange.bind(this, event, variationId)}/></td>
                        <td><Field type="text" name={"sku"} className={'form-row__input'} component="input"/></td>
                        <td><Field type="text" name={"quantity"} className={'form-row__input'} component="input"/></td>
                        <td><Field type="text" name={"stockMode"} className={'form-row__input'} component="input"/></td>
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
