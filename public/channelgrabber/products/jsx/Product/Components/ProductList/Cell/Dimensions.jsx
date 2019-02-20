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
        distanceFromLeftSideOfTableToStartOfCell: '',
        detail: {}
    };

    getValueForDetail = (row, detail) => {
        let detailForId = this.props.detail[detail].byProductId[row.id];
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

    renderInput = (row, detailForInput, value) => {
        const {
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            detail
        } = this.props;

        let portalSettingsForSubmits = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.DIMENSIONS_INPUT_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            detailForInput,
            allRows: this.props.rows.allIds
        });

        let isEditing = detail[detailForInput].byProductId[row.id] ? detail[detailForInput].byProductId[row.id].isEditing : false;
        return (
            <StyledSafeInputStateless
                name={detailForInput}
                step="0.1"
                submitCallback={this.props.actions.saveDetail.bind(this, row, detailForInput)}
                cancelInput={this.props.actions.cancelInput.bind(this, row, detailForInput)}
                setIsEditing={this.props.actions.setIsEditing.bind(this, row.id, detailForInput)}
                onValueChange={this.props.actions.changeDetailValue.bind(this, row.id, detailForInput)}
                submitsPortalSettings={portalSettingsForSubmits}
                width={45}
                placeholder={detailForInput.substring(0, 1)}
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