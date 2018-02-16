define([
    'react',
    'Product/Components/Checkbox',
    'Common/Components/CurrencyInput'
], function(
    React,
    Checkbox,
    CurrencyInput
) {
    "use strict";

    var VariationPicker = React.createClass({
        getDefaultProps: function() {
            return {
                variationsDataForProduct: [],
                currency: '£'
            }
        },
        getInitialState: function() {
            return {
                variationsFormState: {},
                allChecked: true
            }
        },
        componentDidMount: function() {
            this.createVariationFormStateFromProps(this.props);
        },
        createVariationFormStateFromProps: function(newProps) {
            var variationsFormState = {};
            for (var variationIndex in newProps.variationsDataForProduct) {
                var currentVariation = newProps.variationsDataForProduct[variationIndex];
                variationsFormState[currentVariation.id] = {
                    checked: true,
                    price: currentVariation.details ? currentVariation.details.price : null
                }
            }

            this.setState({
                variationsFormState: variationsFormState
            });
        },
        componentDidUpdate: function(prevProps, prevState) {
            var listingFormVariationState = {};

            for (var variationId in this.state.variationsFormState) {
                var currentVariation = this.state.variationsFormState[variationId];

                if (currentVariation.checked == false) {
                    continue;
                }

                listingFormVariationState[variationId] = {
                    price: currentVariation.price
                }
            }
            console.log('didUpdate', listingFormVariationState);
            this.props.setFormStateListing({variations: listingFormVariationState})
        },
        onCheckBoxClick: function(variationId) {
            var variationsFormState = Object.assign({}, this.state.variationsFormState);
            variationsFormState[variationId].checked = ! variationsFormState[variationId].checked;

            var allChecked = true;
            for (var variationId in variationsFormState) {
                if (variationsFormState[variationId].checked == false) {
                    allChecked = false;
                    break;
                }
            }

            this.setState({
                variationsFormState: variationsFormState,
                allChecked: allChecked
            })
        },
        onCheckAll: function() {
            var variationsFormState = Object.assign({}, this.state.variationsFormState);

            var newCheckedState = this.state.allChecked ? false : true;
            for (var variationId in variationsFormState) {
                variationsFormState[variationId].checked = newCheckedState;
            }

            this.setState({
                variationsFormState: variationsFormState,
                allChecked: ! this.state.allChecked
            })
        },
        shouldComponentUpdate(nextProps, nextState) {
            console.log('shouldUpdate', nextState.variationsFormState != this.state.variationsFormState);
            return nextState.variationsFormState != this.state.variationsFormState;
        },
        onVariationValueChange(variationId, fieldName, event) {
            var variationsFormState = Object.assign({}, this.state.variationsFormState);
            variationsFormState[variationId][fieldName] = event.target.value;

            this.setState({
                variationsFormState: variationsFormState
            })
        },
        renderVariationRows: function () {
            return this.props.variationsDataForProduct.map(function(variation) {
                return <tr>
                    <td>
                        <Checkbox
                            id={variation.id}
                            onClick={this.onCheckBoxClick.bind(this, variation.id)}
                            isChecked={this.state.variationsFormState[variation.id] ? this.state.variationsFormState[variation.id].checked : null}
                        />
                    </td>
                    <td>{variation.sku}</td>
                    <td>
                        <CurrencyInput
                            value={this.state.variationsFormState[variation.id]? this.state.variationsFormState[variation.id].price: null}
                            onChange={this.onVariationValueChange.bind(this, variation.id, 'price')}
                            currency={this.props.currency}
                        />
                    </td>
                </tr>
            }.bind(this));
        },
        render: function() {
            return (
                <div>
                    <table>
                        <tr>
                            <td><Checkbox onClick={this.onCheckAll} isChecked={this.state.allChecked} /></td>
                            <td>sku</td>
                            <td>price</td>
                        </tr>
                        {this.renderVariationRows()}
                    </table>
                </div>
            );
        }
    });

    return VariationPicker;
});
