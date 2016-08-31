define(['react', 'Product/Components/Input'], function (React, Input) {
    "use strict";

    var DimensionsRowComponent = React.createClass({
        displayName: 'DimensionsRowComponent',

        getValues: function (variation) {
            return [React.createElement(
                'td',
                { key: 'weight', className: 'detail' },
                React.createElement(Input, { name: 'weight', initialValue: variation.details ? variation.details.weight : '', step: '0.1', submitCallback: this.update })
            ), React.createElement(
                'td',
                { key: 'height', className: 'detail' },
                React.createElement(Input, { name: 'height', initialValue: variation.details ? variation.details.height : '', step: '0.1', submitCallback: this.update })
            ), React.createElement(
                'td',
                { key: 'width', className: 'detail' },
                React.createElement(Input, { name: 'width', initialValue: variation.details ? variation.details.width : '', step: '0.1', submitCallback: this.update })
            ), React.createElement(
                'td',
                { key: 'length', className: 'detail' },
                React.createElement(Input, { name: 'length', initialValue: variation.details ? variation.details.length : '', step: '0.1', submitCallback: this.update })
            )];
        },
        update: function (detail, value) {
            if (this.props.variation === null) {
                return;
            }
            n.notice('Updating ' + detail + ' value.');
            return new Promise(function (resolve, reject) {
                $.ajax({
                    url: '/products/details/update',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: this.props.variation.details.id,
                        detail: detail,
                        value: value,
                        sku: this.props.variation.sku
                    },
                    success: function () {
                        n.success('Successfully updated ' + detail + '.');
                        var dimensionUpdatedEvent = new CustomEvent('dimension-' + this.props.variation.sku, { 'detail': { 'value': value, 'dimension': detail } });
                        window.dispatchEvent(dimensionUpdatedEvent);
                        resolve({ savedValue: value });
                    }.bind(this),
                    error: function (error) {
                        n.error("There was an error when attempting to update the " + detail + ".");
                        reject(new Error(error));
                    }
                });
            }.bind(this));
        },
        getDefaultProps: function () {
            return {
                variation: null
            };
        },
        componentDidMount: function () {
            window.addEventListener('dimension-' + this.props.variation.sku, this.props.dimensionUpdated);
        },
        componentWillUnmount: function () {
            window.removeEventListener('dimension-' + this.props.variation.sku, this.props.dimensionUpdated);
        },
        render: function () {
            return React.createElement(
                'tr',
                null,
                this.getValues(this.props.variation)
            );
        }
    });

    return DimensionsRowComponent;
});
