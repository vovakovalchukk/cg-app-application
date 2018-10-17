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
                distanceFromLeftSideOfTableToStartOfCell
            } = paramObj;
            if (!hasRowBeenRendered(rowIndex)) {
                return;
            }
            let WrapperForSubmits = getWrapperForSubmits({
                elemType,
                distanceFromLeftSideOfTableToStartOfCell: distanceFromLeftSideOfTableToStartOfCell,
                width,
                rowIndex
            });
            let portalSettings = {
                id: rowIndex,
                usePortal: true,
                domNodeForSubmits: getDomNodeForAddingSubmitsTo(rowIndex),
                distanceFromLeftSideOfTableToStartOfCell: distanceFromLeftSideOfTableToStartOfCell + (width / 2),
                PortalWrapper: WrapperForSubmits
            };
            return portalSettings;
        }
    };

    function getWrapperForSubmits({elemType, distanceFromLeftSideOfTableToStartOfCell, width, rowIndex}) {
        let createWrapper = wrapperStyle => {
            return ({children}) => (
                // todo change classNames to have the column in there as well for debug purposes
                <div style={wrapperStyle} className={'submits-container submits-for-row-' + rowIndex}>
                    {children}
                </div>
            );
        };
        if (elemType !== elementTypes.INPUT_SAFE_SUBMITS) {
            return createWrapper({});
        }
        let wrapperStyle = {
            background: 'white',
            width: '60px',
            border: 'solid blue 3px',
            'box-sizing':'border-box',
            'z-index': '100',
            position: 'absolute',
            top: '20px',
            left: distanceFromLeftSideOfTableToStartOfCell + (width / 2) + 'px',
            transform: 'translateX(-50%)'
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