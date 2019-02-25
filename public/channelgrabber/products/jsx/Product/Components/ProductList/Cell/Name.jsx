import React from 'react';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import utility from 'Product/Components/ProductList/utility';
import styled from 'styled-components';
import layoutSettings from 'Product/Components/ProductList/Config/layoutSettings';

let NameContainer = styled.div`
    display:flex;
    align-items:center;
    height:100%;
    padding-left: ${layoutSettings.columnPadding};
    padding-right: ${layoutSettings.columnPadding};
`;
let TextAreaContainer = styled.div`
  width:100%;
  height:100%;
  display:flex;
  align-items: center;
`;
let TextArea = styled.textarea`
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
    render() {
        const {products, rowIndex, actions, name} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);
        const isVariation = stateUtility.isVariation(row);



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
            </TextAreaContainer>
        )
    }
}

export default NameCell;