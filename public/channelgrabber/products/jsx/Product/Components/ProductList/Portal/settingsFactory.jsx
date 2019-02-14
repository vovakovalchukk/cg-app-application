import React from 'react';
import constants from "../Config/constants";
import elementTypes from 'Product/Components/ProductList/Portal/elementTypes'

const distanceDimensionMap = {
    height: 20,
    width: 105,
    length: 192
};
const distanceElementMap = {
    // Hard coding the distances because we couldn't portal submits to a cell dom node due to z-index issues.
    // We had to portal the element to become a sibling of the row to get around this and hence needed to absolutely position
    // relative to the start of the row (at 0px).
    [elementTypes.INPUT_SAFE_SUBMITS]: ({distanceFromLeftSideOfTableToStartOfCell, width}) => (distanceFromLeftSideOfTableToStartOfCell + (width / 2)),
    [elementTypes.STOCK_MODE_SELECT_DROPDOWN]: ({distanceFromLeftSideOfTableToStartOfCell}) => (distanceFromLeftSideOfTableToStartOfCell + 15),
    [elementTypes.SELECT_DROPDOWN]: ({distanceFromLeftSideOfTableToStartOfCell}) => {
        return distanceFromLeftSideOfTableToStartOfCell
    },
    [elementTypes.DIMENSIONS_INPUT_SUBMITS]: ({distanceFromLeftSideOfTableToStartOfCell, detailForInput}) => (distanceFromLeftSideOfTableToStartOfCell + getAddedDistanceForDimensionInput(detailForInput))
};
const elemTypeZIndexMap = {
    [elementTypes.SELECT_DROPDOWN]: 150,
    [elementTypes.STOCK_MODE_SELECT_DROPDOWN]: 150
};
const translateElementMap = {
    [elementTypes.INPUT_SAFE_SUBMITS]: 'translateX(-50%)',
    [elementTypes.STOCK_MODE_SELECT_DROPDOWN]: ''
};

let portalSettingsFactory = (function() {
    return {
        createPortalSettings: function(paramObj) {
            let {
                elemType,
                rowIndex,
                width,
                distanceFromLeftSideOfTableToStartOfCell,
                detailForInput,
                allRows
            } = paramObj;

            if (allRows && allRows.indexOf(rowIndex) < 0) {
                return;
            }

            let domNodeForSubmits = getDomNodeForAddingSubmitsTo(rowIndex);
            if (!domNodeForSubmits) {
                return;
            }

            let WrapperForPortal = getWrapperForPortal({
                elemType,
                distanceFromLeftSideOfTableToStartOfCell,
                width,
                detailForInput,
                translateProp: getTranslateProp({elemType}),
                rowIndex
            });

            return {
                id: rowIndex,
                usePortal: true,
                domNodeForSubmits,
                PortalWrapper: WrapperForPortal
            };
        }
    };

    function getTranslateProp({elemType}) {
        return translateElementMap[elemType];
    }

    function getDistanceFromLeftSideOfTableToStartOfPortal({distanceFromLeftSideOfTableToStartOfCell, width, elemType, detailForInput}) {
        return distanceElementMap[elemType]({distanceFromLeftSideOfTableToStartOfCell, width, detailForInput});
    }

    function getZIndexForWrapper(elemType) {
        if (!elemTypeZIndexMap[elemType]) {
            return 100;
        }
        return elemTypeZIndexMap[elemType];
    }

    function getWrapperForPortal({elemType, distanceFromLeftSideOfTableToStartOfCell, width, detailForInput, rowIndex, translateProp}) {
        let createWrapper = wrapperStyle => {
            return ({children}) => (
                <div style={wrapperStyle}>
                    {children}
                </div>
            );
        };

        let distanceFromLeftSideOfTableToStartOfPortal = getDistanceFromLeftSideOfTableToStartOfPortal({
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            elemType,
            detailForInput
        });

        let zIndexForWrapper = getZIndexForWrapper(elemType);

        let wrapperStyle = {
            background: 'white',
            'box-sizing': 'border-box',
            zIndex: zIndexForWrapper,
            position: 'absolute',
            top: '2.7rem',
            left: distanceFromLeftSideOfTableToStartOfPortal + 'px',
            transform: translateProp
        };

        return createWrapper(wrapperStyle);
    }

    function getDomNodeForAddingSubmitsTo(rowIndex) {
        let targetClass = getClassOfCurrentRow(rowIndex);
        let targetRow = document.querySelector(targetClass);
        if (!targetRow) {
            return;
        }
        return targetRow.parentNode;
    }

    function getClassOfCurrentRow(rowIndex) {
        return '.' + constants.ROW_CLASS_PREFIX + '-' + rowIndex;
    }
}());

export default portalSettingsFactory

function getAddedDistanceForDimensionInput(dimension) {
    return distanceDimensionMap[dimension];
}