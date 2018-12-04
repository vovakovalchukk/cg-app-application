import React from 'react';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';

class TextCell extends React.Component {

    
    static defaultProps = {};
    state = {};

    getUniqueClassName = () => {
        return 'js-' + this.props.columnKey + '-' + this.props.rowIndex;
    };
    getClasses = () => {
        return this.getUniqueClassName() + ' ' + this.props.className;
    };
    componentDidMount() {
        new Clipboard('div.' + this.getUniqueClassName(), [], 'data-copy');
    }
    render() {
        let cellData = stateUtility.getCellData(
            this.props.products,
            this.props.columnKey,
            this.props.rowIndex
        );

        return (
            <div className={this.getClasses()} data-copy={cellData}>
                {cellData}
            </div>
        );
    }
}

export default TextCell;