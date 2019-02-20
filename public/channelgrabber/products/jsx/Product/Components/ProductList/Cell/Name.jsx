import React from 'react';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components';
import LinesEllipsis from 'react-lines-ellipsis'
import layoutSettings from 'Product/Components/ProductList/Config/layoutSettings';
import SafeInputStateless from 'Common/Components/SafeInputStateless';


let NameContainer = styled.div`
    display:flex;
    align-items:center;
    height:100%;
    padding-left: ${layoutSettings.columnPadding};
    padding-right: ${layoutSettings.columnPadding};
`;

class NameCell extends React.Component {
    static defaultProps = {};
    state = {};

    getVariationAttributeArray = (row) => {
        return Object.keys(row.attributeValues).map((key) => {
            return key + ': ' + row.attributeValues[key];
        });
    };
    getVariationName = (row, isVariation) => {
        if (!isVariation) {
            return row['name'];
        }
        let variationAttributeArray = this.getVariationAttributeArray(row)
        return variationAttributeArray.join(', ');
    };
    getUniqueClassName = () => {
        return 'js-' + this.props.columnKey + '-' + this.props.rowIndex;
    };
    getClassNames = () => {
        return this.props.className + ' ' + this.getUniqueClassName();
    };
    componentDidMount = () => {
        new Clipboard('div.' + this.getUniqueClassName(), [], 'data-copy');
    };
    render() {
        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        const isVariation = stateUtility.isVariation(row);

        let name = this.getVariationName(row, isVariation);

        return (
            <SafeInputStateless
                borderless={true}

            />
        )
//        return (
//            <NameContainer>
//                <LinesEllipsis
//                    text={name}
//                    maxLine='2'
//                    ellipsis='...'
//                    trimRight
//                    basedOn='letters'
//                    title={name}
//                />
//            </NameContainer>
//        );
    }
}

export default NameCell;