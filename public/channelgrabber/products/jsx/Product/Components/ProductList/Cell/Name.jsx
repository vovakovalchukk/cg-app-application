import React from 'react';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import utility from 'Product/Components/ProductList/utility';
import styled from 'styled-components';
import layoutSettings from 'Product/Components/ProductList/Config/layoutSettings';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";
import SafeSubmits from 'Common/Components/SafeSubmits';

import ReactDOM from "react-dom";

const StyledSafeSubmits = styled(SafeSubmits)`
    position: absolute;
    transform: translateX(-50%);
`;
const TextAreaContainer = styled.div`
  width:100%;
  height:100%;
  display:flex;
  align-items: center;
  padding-left: ${layoutSettings.columnPadding};
  padding-right: ${layoutSettings.columnPadding};
`;
const TextArea = styled.textarea`
    background:none;
    resize: none;
    box-sizing: border-box;
    overflow: hidden;
    border:none;
    &:hover{
        outline-color:rgb(97, 180, 224);
        outline-offset:-1.81818px;
        outline-style: solid;
        outline-width: 0.909091px;
        border:none;
    }
    &:focus{
        outline-color:rgb(97, 180, 224);
        outline-offset:-1.81818px;
        outline-style: solid;
        outline-width: 0.909091px;
        outline: 1px solid #61b4e0;
        border:none;
    }
`;

const COLS = 32;

class NameCell extends React.Component {
    static defaultProps = {};

    getVariationAttributeArray = (row) => {
        return Object.keys(row.attributeValues).map((key) => {
            return key + ': ' + row.attributeValues[key];
        });
    };
    getProductName = (row, productName) => {
        let {name} = this.props;

        if(name.focusedId === row.id){
            return productName.value;
        }
        return productName.shortenedValue;
    };
    getVariationName(row){
        let variationAttributeArray = this.getVariationAttributeArray(row);
        let nameStr = variationAttributeArray.join(', ');
        //todo - remove this hack
        nameStr = "lorem sdoifsodfjsoidgsidugjadsogdo oisjdfsoijfdsodfi jo ji lorem sdoifsodfjsoidgsidugjadsogdo oisjdfsoijfdsodfi jo ji lorem sdoifsodfjsoidgsidugjadsogdo oisjdfsoijfdsodfi jo ji"

        return nameStr;
    }
    getUniqueClassName = () => {
        return 'js-' + this.props.columnKey + '-' + this.props.rowIndex;
    };
    getClassNames = () => {
        return this.props.className + ' ' + this.getUniqueClassName();
    };
    componentDidMount = () => {
        new Clipboard('div.' + this.getUniqueClassName(), [], 'data-copy');
    };
    isActive() {
        return this.state.value !== this.state.origVal;
    }
    createSubmits({rowIndex, distanceFromLeftSideOfTableToStartOfCell, width, isEditing}){
        if(!isEditing){
            return <span/>
        }

        let portalSettings = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });

        if(!portalSettings){
            // will return undefined if editing is true and no rows have rendered yet
            return <span />
        }

        return ReactDOM.createPortal(
            (
                <portalSettings.PortalWrapper>
                    <StyledSafeSubmits
                        renderOptions={this.renderOptions}
                    />
                </portalSettings.PortalWrapper>
            ),
            portalSettings.domNodeForSubmits
        );
    }
    render() {
        const {products, rowIndex, actions, name, distanceFromLeftSideOfTableToStartOfCell, width} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        const isVariation = stateUtility.isVariation(row);

        let productName = name.names.byProductId[row.id];
        let isEditing = productName.originalValue !== productName.value;
        let Submits = this.createSubmits({rowIndex, distanceFromLeftSideOfTableToStartOfCell, width, isEditing});

        if(isVariation){
            let variationName = this.getVariationName(row);
            return (
                <TextAreaContainer>
                    <div title={variationName}>
                        {utility.shortenNameForCell(variationName) }
                    </div>
                </TextAreaContainer>
            );
        }

        let nameValue = this.getProductName(row, productName);

        return (
            <TextAreaContainer>
                <TextArea
                    cols={COLS}
                    rows={2}
                    onFocus={actions.focusName.bind(this, row.id)}
                    onBlur={actions.blurName.bind(this, row.id)}
                    value={nameValue}
                    onChange={actions.changeName.bind(this, row.id)}
                />
                {Submits}
            </TextAreaContainer>
        )
    }
}

export default NameCell;