import React from 'react';
import Clipboard from 'Clipboard';
import FixedDataTable from 'fixed-data-table-2';
import stateUtility from 'Product/Components/ProductList/stateUtility';

class NameCell extends React.Component {
    static defaultProps = {};
    state = {};

    getVariationName = (row) => {
        return Object.keys(row.attributeValues).map((key) => {
            return (
                <div>{key}: {row.attributeValues[key]}</div>
            );
        });
    };

    componentDidMount() {
        new Clipboard('div.' + this.getUniqueClassName(), [], 'data-copy');
    }

    getUniqueClassName = () => {
        return 'js-' + this.props.columnKey + '-' + this.props.rowIndex;
    };

    getClassNames = () => {
        return this.props.className + ' ' + this.getUniqueClassName();
    };

    render() {
        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        const isVariation = stateUtility.isVariation(row);
        let name = isVariation ? this.getVariationName(row) : row['name'];
        return (
            <div {...this.props} className={this.getClassNames()} data-copy={name}>
                {name}
            </div>
        );
    }
}

export default NameCell;