import React from 'react';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import utility from 'Product/Components/ProductList/utility';
import styled from 'styled-components';
import layoutSettings from 'Product/Components/ProductList/Config/layoutSettings';
import portalSettingsFactory from "../Portal/settingsFactory";
import elementTypes from "../Portal/elementTypes";
import portalFactory from "../Portal/portalFactory";

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
    getProductName = (row, isVariation) => {
        let {name} = this.props;
        let productName = name.names.byProductId[row.id];

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
        let portalSettingsForSubmits = portalSettingsFactory.createPortalSettings({
            elemType: elementTypes.INPUT_SAFE_SUBMITS,
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width,
            allRows: this.props.rows.allIds
        });

        let Submits = <span></span>;
        if (portalSettingsForSubmits) {
            Submits = portalFactory.createPortal({
                portalSettings: portalSettingsForSubmits,
                Component: StyledSafeSubmits,
                componentProps: {
                    isEditing,
                    submitInput: this.submitInput,
                    cancelInput: this.cancelInput
                }
            });
        }
        return Submits;
    }
    render() {
        const {products, rowIndex, actions, name, distanceFromLeftSideOfTableToStartOfCell, width} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        const isVariation = stateUtility.isVariation(row);

        // todo stop hardcoding this
        let isEditing = true;
        let

        let Submits = this.createSubmits({
            rowIndex,
            distanceFromLeftSideOfTableToStartOfCell,
            width
        });

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

        return (
            <TextAreaContainer>
                <TextArea
                    cols={COLS}
                    rows={2}
                    onFocus={actions.focusName.bind(this, row.id)}
                    onBlur={actions.blurName.bind(this, row.id)}
                    value={this.getProductName(row, isVariation)}
                    onChange={actions.changeName.bind(this, row.id)}
                />
                {Submits}
            </TextAreaContainer>
        )
    }
}

export default NameCell;