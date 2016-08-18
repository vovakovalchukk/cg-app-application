define([
    'react',
    'Product/Components/Input'
], function(
    React,
    Input
) {
    "use strict";

    var DimensionsRowComponent = React.createClass({
        getValues: function(variation) {
            return [
                <td key="weight" className="detail">
                    <Input name='weight' value={variation.details ?variation.details.weight: ''} step="0.1" submitCallback={this.update}/>
                </td>,
                <td key="height" className="detail">
                    <Input name='height' value={variation.details ?variation.details.height: ''} step="0.1" submitCallback={this.update}/>
                </td>,
                <td key="width" className="detail">
                    <Input name='width' value={variation.details ?variation.details.width: ''} step="0.1" submitCallback={this.update}/>
                </td>,
                <td key="length" className="detail">
                    <Input name='length' value={variation.details ?variation.details.length: ''} step="0.1" submitCallback={this.update}/>
                </td>,
            ];
        },
        update: function(detail, value) {
            if (this.props.variation === null) {
                return;
            }
            console.log('Submitting dimension '+detail+' as '+value+' to '+this.props.updateUrl);
            return;
            $.ajax({
                url: this.props.updateUrl,
                type: 'POST',
                dataType : 'json',
                body: {
                    id: this.props.variation.id,
                    detail: detail,
                    value: value,
                    sku: this.props.variation.sku
                },
                success: function() {
                    return true;
                },
                error: function() {
                    return false;
                }
            });
        },
        getDefaultProps: function() {
            return {
                variation: null
            };
        },
        render: function () {
            return <tr>{this.getValues(this.props.variation)}</tr>;
        }
    });

    return DimensionsRowComponent;
});