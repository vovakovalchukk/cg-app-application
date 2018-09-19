define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility',
    'Common/Components/SafeInput'
], function(
    React,
    FixedDataTable,
    stateUtility,
    Input
) {
    "use strict";
    
    let WeightCell = React.createClass({
        getDefaultProps: function() {
            return {
                products: {},
                rowIndex: null
            };
        },
        getInitialState: function() {
            return {};
        },
        // update: function(detail, value) {
        //     if (this.props.variation === null) {
        //         return;
        //     }
        //     n.notice('Updating '+detail+' value.');
        //     return new Promise(function(resolve, reject) {
        //         $.ajax({
        //             url: '/products/details/update',
        //             type: 'POST',
        //             dataType : 'json',
        //             data: {
        //                 id: this.props.variation.details.id,
        //                 detail: detail,
        //                 value: value,
        //                 sku: this.props.variation.sku
        //             },
        //             success: function() {
        //                 n.success('Successfully updated '+detail+'.');
        //                 window.triggerEvent('dimension-'+this.props.variation.sku, {'value': value, 'dimension': detail});
        //                 resolve({ savedValue: value });
        //             }.bind(this),
        //             error: function(error) {
        //                 n.showErrorNotification(error, "There was an error when attempting to update the "+detail+".");
        //                 reject(new Error(error));
        //             }
        //         });
        //     }.bind(this));
        // },
        render() {
            // console.log('in WeightCell with this.props:  ', this.props);
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            
            const isSimpleProduct = stateUtility.isSimpleProduct(row)
            const isVariation = stateUtility.isVariation(row);
            
            if (!isSimpleProduct && !isVariation) {
                //todo - remove the text here before submission
                return <span></span>
            }
            
            //todo - change the input values to reflect what is coming back from the store
            
            //replace variation with product
            return (
                <span>
                    in weight cell
                    <Input
                        name='weight'
                        initialValue={(row.details && row.details.weight) ? parseFloat(row.details.weight).toFixed(3): ''}
                        step="0.1"
                        submitCallback={this.props.actions.saveDetail.bind(this,row)}
                    />
                </span>
            );
        }
    });
    
    return WeightCell;
});
