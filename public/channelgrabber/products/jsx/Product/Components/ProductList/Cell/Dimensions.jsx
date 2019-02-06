import React from 'react';
import styled from 'styled-components';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import SafeInputStateless from 'Common/Components/SafeInputStateless';
import elementTypes from "../Portal/elementTypes";
import portalSettingsFactory from "../Portal/settingsFactory";

const InputsContainer = styled.div`
    display: flex;
    justify-content: center;
    align-items: center;
`;
const StyledSafeInputStateless = styled(SafeInputStateless)`
    display:inline-block,
`;
const Cross = styled.span`
    margin-left:3px;
    margin-right:3px;
`;

class DimensionsCell extends React.Component {
    static defaultProps = {
        products: {},
        dimensions: {},
        rows: [],
        rowIndex: null,
        actions: {},
        width: '',
        distanceFromLeftSideOfTableToStartOfCell: ''
    };

    getValueForDetail = (row, detail) => {
        let detailForId = this.props.dimensions[detail].byProductId[row.id];
        if (!detailForId) {
            return row.details[detail];
        }
        if (typeof detailForId.valueEdited === "string") {
            return detailForId.valueEdited;
        }
        if (typeof detailForId.value === "string") {
            return detailForId.value;
        }
        return row.details[detail];
    };

    shouldRenderSubmits = () => {
        return !this.props.scroll.userScrolling;
    };

    renderInput = (row, detail, value) => {
        const {
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            dimensions
        } = this.props;

        let dimension = detail;
        let portalSettingsForSubmits = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.DIMENSIONS_INPUT_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            dimension,
            allRows: this.props.rows.allIds
        });

        let isEditing = dimensions[detail].byProductId[row.id] ? dimensions[detail].byProductId[row.id].isEditing : false;
        return (
            <StyledSafeInputStateless
                name={detail}
                step="0.1"
                submitCallback={this.props.actions.saveDetail.bind(this, row, detail)}
                cancelInput={this.props.actions.cancelInput.bind(this, row, detail)}
                setIsEditing={this.props.actions.setIsEditing.bind(this, row.id, detail)}
                onValueChange={this.props.actions.changeDimensionValue.bind(this, row.id, detail)}
                submitsPortalSettings={portalSettingsForSubmits}
                width={45}
                placeholder={detail.substring(0, 1)}
                value={value}
                isEditing={isEditing}
                shouldRenderSubmits={this.shouldRenderSubmits()}
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

        let valueForHeight = this.getValueForDetail(row, 'height');
        let valueForWidth = this.getValueForDetail(row, 'width');
        let valueForLength = this.getValueForDetail(row, 'length');

        return (
            <InputsContainer className={this.props.className}>
                {this.renderInput(row, 'height', valueForHeight)}
                <Cross>✕</Cross>
                {this.renderInput(row, 'width', valueForWidth)}
                <Cross>✕</Cross>
                {this.renderInput(row, 'length', valueForLength)}
            </InputsContainer>
        );
    }
}

export default DimensionsCell;