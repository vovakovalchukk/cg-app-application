import React from 'react';
import styled from 'styled-components';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import Input from 'Common/Components/SafeInput';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";

const InputsContainer = styled.div`
    display: flex;
    justify-content: center;
    align-items: center;
`;
const StyledInput = styled(Input)`
    display:inline-block,
`;
const Cross = styled.span`
    margin-left:3px;
    margin-right:3px;
`;

class DimensionsCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null
    };

    state = {};

    renderInput = (row, detail) => {
        const {rowIndex, distanceFromLeftSideOfTableToStartOfCell, width, visibleRows} = this.props;

        let dimension = detail;
        let portalSettingsForSubmits = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.DIMENSIONS_INPUT_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            dimension,
            allRows: this.props.rows.allIds
        });

        return (
            <StyledInput
                name={detail}
                initialValue={(row.details && row.details[detail]) ? row.details[detail] : detail.substring(0, 1)}
                step="0.1"
                submitCallback={this.props.actions.saveDetail.bind(this, row)}
                submitsPortalSettings={portalSettingsForSubmits}
                width={45}
                placeholder={detail.substring(0, 1)}
            />
        )
    };

    render() {
        const {products, rowIndex} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);

        const isSimpleProduct = stateUtility.isSimpleProduct(row)
        const isVariation = stateUtility.isVariation(row);

        if (!isSimpleProduct && !isVariation) {
            return <span></span>
        }

        return (
            <InputsContainer className={this.props.className}>
                {this.renderInput(row, 'height')}
                <Cross>✕</Cross>
                {this.renderInput(row, 'width')}
                <Cross>✕</Cross>
                {this.renderInput(row, 'length')}
            </InputsContainer>
        );
    }
}

export default DimensionsCell;