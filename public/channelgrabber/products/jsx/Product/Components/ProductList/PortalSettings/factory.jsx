import React from 'react';
import constants from "../Config/constants";
import utility from "../utility";
import elementTypes from 'Product/Components/ProductList/PortalSettings/elementTypes'

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

            if (!hasRowBeenRendered(rowIndex)) {
                return;
            }

            let WrapperForSubmits = getWrapperForSubmits({
                elemType,
                distanceFromLeftSideOfTableToStartOfPortal: getDistanceFromLeftSideOfTableToStartOfPortal({
                    distanceFromLeftSideOfTableToStartOfCell,
                    width,
                    elemType,
                    dimension
                }),
                translateProp: getTranslateProp({elemType})
            });

            let portalSettings = {
                id: rowIndex,
                usePortal: true,
                domNodeForSubmits: getDomNodeForAddingSubmitsTo(rowIndex),
                PortalWrapper: WrapperForSubmits
            };
            return portalSettings;
        }
    };

    function getTranslateProp({elemType}){
        let translateElementMap = {}
        translateElementMap[ elementTypes['INPUT_SAFE_SUBMITS'] ] = 'translateX(-50%)';
        translateElementMap[ elementTypes['STOCK_MODE_SELECT_DROPDOWN']] = '';
        return translateElementMap[elemType];
    }

    function getAddedDistanceForDimensionInput(dimension){
        let distanceDimensionMap = {};
        distanceDimensionMap[ "height" ] = 10;
        distanceDimensionMap[ "width" ] = 85;
        distanceDimensionMap[ "length" ] = 160;
        return distanceDimensionMap[dimension];
    }

    function getDistanceFromLeftSideOfTableToStartOfPortal({distanceFromLeftSideOfTableToStartOfCell, width, elemType, dimension}) {
        let distanceElementMap = {}
        distanceElementMap[ elementTypes['INPUT_SAFE_SUBMITS'] ] = distanceFromLeftSideOfTableToStartOfCell + (width / 2);
        // Somewhat hard coding the distance here due to a lack of simple alternatives.
        // These will need to be changed if you change the width of the containing cells.
        distanceElementMap[ elementTypes['STOCK_MODE_SELECT_DROPDOWN']] = distanceFromLeftSideOfTableToStartOfCell + 27;
        distanceElementMap[ elementTypes['SELECT_DROPDOWN']] = distanceFromLeftSideOfTableToStartOfCell;
        distanceElementMap[ elementTypes['DIMENSIONS_INPUT_SUBMITS']] = distanceFromLeftSideOfTableToStartOfCell + getAddedDistanceForDimensionInput(dimension);

        return distanceElementMap[elemType];
    }

    function getWrapperForSubmits({elemType, distanceFromLeftSideOfTableToStartOfPortal, width, rowIndex, translateProp}) {
        let createWrapper = wrapperStyle => {
            return ({children}) => (
                // todo change classNames to have the column in there as well for debug purposes
                <div style={wrapperStyle} className={'submits-container submits-for-row-' + rowIndex}>
                    {children}
                </div>
            );
        };
        let wrapperStyle = {
            background: 'white',
            'box-sizing': 'border-box',
            'z-index': '100',
            position: 'absolute',
            top: '2.25rem',
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
//        console.log('{targetRow,targetClass}: ', {targetRow,targetClass});
        let targetNode = targetRow.parentNode;
        return targetNode;
    }

    function getClassOfCurrentRow(rowIndex) {
        return '.' + constants.ROW_CLASS_PREFIX + '-' + rowIndex;
    }
}());

export default portalSettingsFactory