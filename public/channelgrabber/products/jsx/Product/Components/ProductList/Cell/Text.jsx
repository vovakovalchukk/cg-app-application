import React from 'react';
import Clipboard from 'Clipboard';
import FixedDataTable from 'fixed-data-table-2';
import stateUtility from 'Product/Components/ProductList/stateUtility';

class TextCell extends React.Component {
    static defaultProps = {};
    state = {};

    componentDidMount() {
        new Clipboard('div.js-' + this.getUniqueClassName(), [], 'data-copy');
    }

    getUniqueClassName = () => {
        return this.props.columnKey + '-' + this.props.rowIndex;
    };

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
}

export default TextCell;