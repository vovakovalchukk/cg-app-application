import React from 'react';
import constants from "../Config/constants";
import elementTypes from 'Product/Components/ProductList/Portal/elementTypes'

const distanceDimensionMap = {
    height: 10,
    width: 85,
    length: 160
};
const distanceElementMap = {
    // hard coding the distances here due to a lack of better alternatives
    [elementTypes.INPUT_SAFE_SUBMITS] : ({distanceFromLeftSideOfTableToStartOfCell, width}) => (distanceFromLeftSideOfTableToStartOfCell + (width / 2)),
    [elementTypes.STOCK_MODE_SELECT_DROPDOWN] : ({distanceFromLeftSideOfTableToStartOfCell}) =>  (distanceFromLeftSideOfTableToStartOfCell + 15),
    [elementTypes.SELECT_DROPDOWN]: ({distanceFromLeftSideOfTableToStartOfCell}) => {return distanceFromLeftSideOfTableToStartOfCell},
    [elementTypes.DIMENSIONS_INPUT_SUBMITS]: ({distanceFromLeftSideOfTableToStartOfCell,dimension}) => (distanceFromLeftSideOfTableToStartOfCell + getAddedDistanceForDimensionInput(dimension))
};
const elemTypeZIndexMap = {
    [elementTypes.SELECT_DROPDOWN]: 150,
    [elementTypes.STOCK_MODE_SELECT_DROPDOWN]: 150
};
const translateElementMap = {
    [elementTypes.INPUT_SAFE_SUBMITS] : 'translateX(-50%)',
    [elementTypes.STOCK_MODE_SELECT_DROPDOWN] : ''
};

let portalSettingsFactory = (function() {
    return {
        createPortalSettings: function(paramObj) {
            let {
                elemType,
                rowIndex,
                width,
                distanceFromLeftSideOfTableToStartOfCell,
                dimension,
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
                dimension,
                translateProp: getTranslateProp({elemType})
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

    function getDistanceFromLeftSideOfTableToStartOfPortal({distanceFromLeftSideOfTableToStartOfCell, width, elemType, dimension}) {
        return distanceElementMap[elemType]({distanceFromLeftSideOfTableToStartOfCell,width,dimension});
    }

    function getZIndexForWrapper(elemType) {
        if (!elemTypeZIndexMap[elemType]) {
            return 100;
        }
        return elemTypeZIndexMap[elemType];
    }

    function getWrapperForPortal({elemType, distanceFromLeftSideOfTableToStartOfCell, width, dimension, rowIndex, translateProp}) {
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
            dimension
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