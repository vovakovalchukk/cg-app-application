define([
    'react',
    'Clipboard',
    'fixed-data-table',
    'Product/Components/ProductList/stateUtility'
], function(
    React,
    Clipboard,
    FixedDataTable,
    stateUtility
) {
    "use strict";
    
    let TextCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        componentDidMount: function() {
            new Clipboard('div.js-' + this.getUniqueClassName(), [], 'data-copy');
        },
        getUniqueClassName: function() {
            return this.props.columnKey + '-' + this.props.rowIndex;
        },
        render() {
            let cellData = stateUtility.getCellData(
                this.props.products,
                this.props.columnKey,
                this.props.rowIndex
            );
            return (
                <div className={'js-' + this.getUniqueClassName()} data-copy={cellData} {...this.props}>
                    {cellData}
                </div>
            );
        }
    });
    
    return TextCell;
});
