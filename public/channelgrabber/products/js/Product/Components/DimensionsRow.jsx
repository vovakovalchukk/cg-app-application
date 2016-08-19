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
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: this.props.updateUrl,
                    type: 'POST',
                    dataType : 'json',
                    data: {
                        id: this.props.variation.details.id,
                        detail: detail,
                        value: value,
                        sku: this.props.variation.sku
                    },
                    success: function() {
                        resolve({ savedValue: value });
                    },
                    error: function(error) {
                        reject(new Error(error));
                    }
                });
            }.bind(this));
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