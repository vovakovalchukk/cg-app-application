import React from 'react';
import ReactDOM from 'react-dom';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import utility from 'Product/Components/ProductList/utility';
import styled from 'styled-components';
import layoutSettings from 'Product/Components/ProductList/Config/layoutSettings';

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
    transition: outline 0.1s ease;
    outline-color: transparent;
    margin-left: -2px;
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

const TEXT_AREA_COLUMN_AMOUNT = 32;

let rowData = {};

class NameCell extends React.Component {
    static defaultProps = {
        products: {},
        rowIndex: null,
        name: {},
        rows: {},
        columnKey: '',
        actions: {}
    };

    getVariationAttributeArray = (row) => {
        return Object.keys(row.attributeValues).map((key) => {
            return key + ': ' + row.attributeValues[key];
        });
    };
    getProductName = (row, productName) => {
        if(this.props.focus.focusedInputInfo.columnKey && (this.props.focus.focusedInputInfo.columnKey === 'name') && (this.props.focus.focusedInputInfo.rowId === row.id)){
            return productName.value;
        }
        return productName.shortenedValue;
    };
    getVariationName(row){
        let variationAttributeArray = this.getVariationAttributeArray(row);
        let nameStr = variationAttributeArray.join(', ');
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
    getInputInfo = (row) => {
        return {
            'rowId': row.id,
            'columnKey': this.props.columnKey
        };
    };
    onFocus = () => {
        let row = rowData[this.props.rowIndex];
        let inputInfo = this.getInputInfo(row);
        this.moveCaretToEndOfTextArea(row);
        this.props.actions.focusInput(inputInfo);
    };
    moveCaretToEndOfTextArea(row) {
        let textArea = ReactDOM.findDOMNode(this.refs[this.getUniqueInputId()]);
        let productName = this.props.name.names.byProductId[row.id];
        let caretPosition = productName.value.length;

        // setting timeout because Chrome sets input focus before adding a caret
        setTimeout(() => {
            textArea.setSelectionRange(caretPosition, caretPosition);
            textArea.scrollTop = textArea.scrollHeight;
        }, 0);
    }
    onBlur = () => {
        let row = rowData[this.props.rowIndex];
        this.props.actions.nameBlur(row.id);
    };
    changeName = (e) => {
        let row = rowData[this.props.rowIndex];
        this.props.actions.changeName(e.target.value, row.id);
    };
    getUniqueInputId = () => {
        let row = rowData[this.props.rowIndex];
        return row.id+'-'+ this.props.columnKey
    };
    render = () => {
        const {products, rowIndex, name} = this.props;

        rowData[rowIndex] = stateUtility.getRowData(products, rowIndex);

        const isVariation = stateUtility.isVariation(rowData[rowIndex]);
        
        if(isVariation){
            let variationName = this.getVariationName(rowData[rowIndex]);
            return (
                <TextAreaContainer>
                    <div title={variationName}>
                        {utility.shortenNameForCell(variationName) }
                    </div>
                </TextAreaContainer>
            );
        }

        let productName = name.names.byProductId[rowData[rowIndex].id];
        let nameValue = this.getProductName(rowData[rowIndex], productName);
        let uniqueInputId = this.getUniqueInputId();

        return (
            <TextAreaContainer>
                <TextArea
                    ref={uniqueInputId}
                    key={uniqueInputId}
                    cols={TEXT_AREA_COLUMN_AMOUNT}
                    rows={2}
                    onFocus={this.onFocus}
                    onBlur={this.onBlur}
                    value={nameValue}
                    title={nameValue}
                    onChange={this.changeName}
                    data-inputinfo={JSON.stringify(this.getInputInfo(rowData[rowIndex]),null,1)}
                />
            </TextAreaContainer>
        )
    };
}

export default NameCell;