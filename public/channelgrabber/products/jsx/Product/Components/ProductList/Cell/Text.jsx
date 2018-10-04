import React from 'react';
import Clipboard from 'Clipboard';
import FixedDataTable from 'fixed-data-table';
import stateUtility from 'Product/Components/ProductList/stateUtility';

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

export default TextCell;