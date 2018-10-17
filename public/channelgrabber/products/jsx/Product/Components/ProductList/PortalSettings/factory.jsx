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
                distanceFromLeftSideOfTableToStartOfPortal: getDistanceFromLeftSideOfTableToStartOfPortal({
                    distanceFromLeftSideOfTableToStartOfCell,
                    width,
                    elemType
                }),
                translateProp: getTranslateProp(elemType)
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

    function getTranslateProp(elemType){
        let translateElementMap = {}
        translateElementMap[ elementTypes['INPUT_SAFE_SUBMITS'] ] = 'translateX(-50%)';
        translateElementMap[ elementTypes['STOCK_MODE_SELECT_DROPDOWN']] = '';

        return translateElementMap[elemType];
    }

    function getDistanceFromLeftSideOfTableToStartOfPortal({distanceFromLeftSideOfTableToStartOfCell, width, elemType}) {
        let distanceElementMap = {}
        distanceElementMap[ elementTypes['INPUT_SAFE_SUBMITS'] ] = distanceFromLeftSideOfTableToStartOfCell + (width / 2);
        // hard coding the distance until a better solution is found
        distanceElementMap[ elementTypes['STOCK_MODE_SELECT_DROPDOWN']] = distanceFromLeftSideOfTableToStartOfCell + 27;

        console.log('{elemType, distanceFromLeftSideOfTableToStartOfCell}: ', {elemType,
            distanceFromLeftSideOfTableToStartOfCell,
            mappedResult: distanceElementMap[elemType]
        });


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
            border: 'solid blue 3px',
            'box-sizing': 'border-box',
            'z-index': '100',
            position: 'absolute',
            top: '20px',
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