define([
    'react',
    'Product/Components/Checkbox'
], function(
    React,
    Checkbox
) {
    "use strict";

    var VariationPicker = React.createClass({
        getDefaultProps: function() {
            return {
                variations: []
            }
        },
        getInitialState: function() {
            return {
                variationsFormState: {},
                allChecked: true
            }
        },
        componentDidMount: function(props, a, b) {
            this.createVariationFormStateFromProps(this.props);
        },
        componentWillReceiveProps: function(newProps) {
            this.createVariationFormStateFromProps(newProps);
        },
        createVariationFormStateFromProps: function(newProps) {
            var variationsFormState = {};
            for (var variationIndex in newProps.variations) {
                var currentVariation = newProps.variations[variationIndex];
                variationsFormState[currentVariation.id] = {checked: true};
            }

            this.setState({
                variationsFormState: variationsFormState
            })
        },
        onCheckBoxClick: function(variationId) {
            var newVariationsState = Object.assign({}, this.state.variationsFormState);
            newVariationsState[variationId].checked = ! newVariationsState[variationId].checked;

            var allChecked = true;
            for (var variationId in newVariationsState) {
                if (newVariationsState[variationId].checked == false) {
                    allChecked = false;
                    break;
                }
            }
            this.setState({
                variationsFormState: newVariationsState,
                allChecked: allChecked
            })
        },
        renderVariationRows: function () {
            return this.props.variations.map(function(variation) {
                return <tr>
                    <td>
                        <Checkbox
                            id={variation.id}
                            onClick={this.onCheckBoxClick.bind(this, variation.id)}
                            isChecked={this.state.variationsFormState[variation.id] ? this.state.variationsFormState[variation.id].checked : true}
                        />
                    </td>
                    <td>{variation.sku}</td>
                    <td>{variation.price}</td>
                </tr>
            }.bind(this));
        },
        onCheckAll: function() {
            var newVariationsState = Object.assign({}, this.state.variationsFormState);

            var newCheckedState = this.state.allChecked ? false : true;
            for (var variationId in newVariationsState) {
                newVariationsState[variationId].checked = newCheckedState;
            }

            this.setState({
                variationsFormState: newVariationsState,
                allChecked: ! this.state.allChecked
            })
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
