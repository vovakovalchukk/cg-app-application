import React from 'react';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import layoutSettings from "../Config/layoutSettings";
import styled from 'styled-components';

const TextContainer = styled.div`
    padding-left: ${layoutSettings.columnPadding};
    padding-right: ${layoutSettings.columnPadding};
`;

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
            <TextContainer className={this.getClasses()} data-copy={cellData}>
                {cellData}
            </TextContainer>
        );
    }
}

export default TextCell;