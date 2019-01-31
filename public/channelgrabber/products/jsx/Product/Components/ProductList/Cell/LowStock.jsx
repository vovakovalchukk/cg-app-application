import React from 'react';
import stateUtility from "../stateUtility";

class StockModeCell extends React.Component {
    static defaultProps = {
    };

    render() {
        const {
            products,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        } = this.props;

        const row = stateUtility.getRowData(products, rowIndex);

        return 'test';
    }
}

export default StockModeCell;