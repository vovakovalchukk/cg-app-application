import React from 'react';
import constants from "../Config/constants";
import utility from "../utility";
import elementTypes from 'Product/Components/ProductList/Portal/elementTypes'

let portalSettingsFactory = (function() {
    return {
        createPortalSettings: function(paramObj) {
            let {
                elemType,
                rowIndex,
                width,
                distanceFromLeftSideOfTableToStartOfCell,
                dimension
            } = paramObj;
//            console.log('in createPortalSettings with paramObj', paramObj);

            if (!hasRowBeenRendered(rowIndex)) {
                console.log('about to break out');
                
                
                return;
            }

            let WrapperForPortal = getWrapperForPortal({
                elemType,
                    distanceFromLeftSideOfTableToStartOfCell,
                    width,
                    elemType,
                    dimension,
                translateProp: getTranslateProp({elemType})
            });

            let portalSettings = {
                id: rowIndex,
                usePortal: true,
                domNodeForSubmits: getDomNodeForAddingSubmitsTo(rowIndex),
                PortalWrapper: WrapperForPortal
            };
            return portalSettings;
        }
    };

    function getTranslateProp({elemType}){
        let translateElementMap = {}
        translateElementMap[ elementTypes.INPUT_SAFE_SUBMITS] = 'translateX(-50%)';
        translateElementMap[ elementTypes.STOCK_MODE_SELECT_DROPDOWN] = '';
        return translateElementMap[elemType];
    }

    function getAddedDistanceForDimensionInput(dimension){
        let distanceDimensionMap = {
            height: 10,
            width: 85,
            length: 160
        };
        return distanceDimensionMap[dimension];
    }

    function getDistanceFromLeftSideOfTableToStartOfPortal({distanceFromLeftSideOfTableToStartOfCell, width, elemType, dimension}) {
        let distanceElementMap = {};
        distanceElementMap[ elementTypes.INPUT_SAFE_SUBMITS ] = distanceFromLeftSideOfTableToStartOfCell + (width / 2);
        // Somewhat hard coding the distance here due to a lack of simple alternatives.
        // These will need to be changed if you change the width of the containing cells.
        distanceElementMap[ elementTypes.STOCK_MODE_SELECT_DROPDOWN] = distanceFromLeftSideOfTableToStartOfCell + 27;
        distanceElementMap[ elementTypes.SELECT_DROPDOWN] = distanceFromLeftSideOfTableToStartOfCell;
        distanceElementMap[ elementTypes.DIMENSIONS_INPUT_SUBMITS] = distanceFromLeftSideOfTableToStartOfCell + getAddedDistanceForDimensionInput(dimension);

        return distanceElementMap[elemType];
    }

    function getZIndexForWrapper(elemType){
        let elemTypeZIndexMap = {
            [elementTypes.SELECT_DROPDOWN] : 150,
            [elementTypes.STOCK_MODE_SELECT_DROPDOWN] : 150
        };
        if(!elemTypeZIndexMap[elemType]){
            return 100;
        }
        return elemTypeZIndexMap[elemType];
    }

    function getWrapperForPortal({elemType, distanceFromLeftSideOfTableToStartOfCell, width, dimension, rowIndex, translateProp}) {
        let createWrapper = wrapperStyle => {
            return ({children}) => (
                // todo change classNames to have the column in there as well for debug purposes
                <div style={wrapperStyle}>
                    {children}
                </div>
            );
        };

        let distanceFromLeftSideOfTableToStartOfPortal =  getDistanceFromLeftSideOfTableToStartOfPortal({
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            elemType,
            dimension
        });

        let zIndexForWrapper = getZIndexForWrapper(elemType);

        let wrapperStyle = {
            background: 'white',
            'box-sizing': 'border-box',
            'z-index': zIndexForWrapper,
            position: 'absolute',
            top: '2.5rem',
            left: distanceFromLeftSideOfTableToStartOfPortal + 'px',
            transform: translateProp
        };

        return createWrapper(wrapperStyle);
    }

    function getAllVisibleRowNodes() {
        let rows = document.getElementsByClassName(constants.ROW_CLASS_PREFIX);
        let rowNodes = [];
        for (var i = 0; i < rows.length; i++) {
            if (i === rows.length) {
                continue;
            }
            rowNodes.push(rows[i]);
        }
        return rowNodes;
    }
    function getArrayOfAllRenderedRows() {
        let allVisibleNonHeaderRows = getAllVisibleRowNodes();
        let allRows = allVisibleNonHeaderRows.map(row => {
            let rowIndex = utility.getRowIndexFromRow(row);
            return rowIndex
        });
        return allRows;
    }

    function hasRowBeenRendered(rowIndex) {
        let allRows = getArrayOfAllRenderedRows();
        let hasBeenRendered = allRows.includes(rowIndex);
//        console.log('hasBeenRendered: ', {
//            hasBeenRendered,
//            allRows,
//            rowIndex: this.props.rowIndex
//        });
        return hasBeenRendered;
    }

    function getDomNodeForAddingSubmitsTo(rowIndex) {
        let targetClass = getClassOfCurrentRow(rowIndex);
        let targetRow = document.querySelector(targetClass);
        let targetNode = targetRow.parentNode;
        return targetNode;
    }

    function getClassOfCurrentRow(rowIndex) {
        return '.' + constants.ROW_CLASS_PREFIX + '-' + rowIndex;
    }
}());

export default portalSettingsFactory