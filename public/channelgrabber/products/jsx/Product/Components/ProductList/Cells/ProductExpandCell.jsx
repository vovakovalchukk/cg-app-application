define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/stateFilters'

], function(
    React,
    FixedDataTable,
    stateFilters
) {
    "use strict";
    
    let ProductExpandCell = React.createClass({
        getDefaultProps: function() {
            return {
                rowData: {},
                rowIndex: null
            };
        },
        getRowData: function() {
            return stateFilters.getRowData(this.props.products, this.props.rowIndex)
        },
        isParentProduct: function(rowData) {
            return rowData.variationCount !== undefined && rowData.variationCount >= 1
        },
        renderExpandIcon: function() {
            let rowData = this.getRowData();
            let isParentProduct = this.isParentProduct(rowData);
            if (!isParentProduct) {
                return;
            }
            if (this.getRowData().expandStatus === 'loading') {
                return 'loading....'
            }
            return (!rowData.expandStatus || rowData.expandStatus === 'collapsed' ? '\u25BA' : '\u25BC')
        },
        onExpandClick: function() {
            let rowData = this.getRowData();
            if (rowData.expandStatus === 'loading') {
                return;
            }
            if (!rowData.expandStatus || rowData.expandStatus === 'collapsed') {
                this.props.actions.expandProduct(rowData.id)
                return;
            }
            this.props.actions.collapseProduct(rowData.id);
        },
        render() {
            return (
                <div {...this.props}>
                    <a onClick={this.onExpandClick}>
                        {this.renderExpandIcon()}
                    </a>
                </div>
            );
        }
    });
    
    return ProductExpandCell;
});
