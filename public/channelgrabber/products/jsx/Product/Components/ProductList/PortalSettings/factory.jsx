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
                distanceFromLeft
            } = paramObj;
            if (!hasRowBeenRendered(rowIndex)) {
                console.log('not creating portal settings since there is no domNode for this.props.rowIndex', rowIndex);
                return;
            }
            let wrapperForSubmits = getWrapperForSubmits({elemType, distanceFromLeft, width, rowIndex});
            let portalSettings = {
                id: rowIndex,
                usePortal: true,
                domNodeForSubmits: getDomNodeForAddingSubmitsTo(rowIndex),
                distanceFromLeft: distanceFromLeft + (width / 2),
                SubmitWrapper: wrapperForSubmits
            };
            return portalSettings;
        }
    };

    function getWrapperForSubmits({elemType, distanceFromLeft, width, rowIndex}) {
        let createWrapper = wrapperStyle => {
            return ({children}) => (
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
            height: '30px',
            border: 'solid blue 3px',
            'z-index': '100',
            position: 'absolute',
            top: '15px',
            left: distanceFromLeft + (width / 2) + 'px',
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
        console.log('{targetRow,targetClass}: ', {targetRow,targetClass});
        let targetNode = targetRow.parentNode;
        return targetNode;
    }
    function getClassOfCurrentRow(rowIndex) {
        return '.' + constants.ROW_CLASS_PREFIX + '-' + rowIndex;
    }
}());

export default portalSettingsFactory