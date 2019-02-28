import React from 'react';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import utility from 'Product/Components/ProductList/utility';
import styled from 'styled-components';
import layoutSettings from 'Product/Components/ProductList/Config/layoutSettings';
import Portaller from "../Portal/Portaller";
import SafeSubmits from 'Common/Components/SafeSubmits';

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
    submitInput = () => {
        let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
        this.props.actions.updateName(row.id);
    };
    cancelInput = () => {
        let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
        this.props.actions.cancelNameEdit(row.id);
    };
    createSubmits({rowIndex, distanceFromLeftSideOfTableToStartOfCell, width, isEditing}){
        if(!isEditing || this.props.name.nameUpdating){
            return <span/>
        }

        return (<Portaller
            rowIndex={rowIndex}
            distanceFromLeftSideOfTableToStartOfCell={distanceFromLeftSideOfTableToStartOfCell}
            width={width}
            allRows={this.props.rows.allIds}
            render= {()=>{
               return (
                    <StyledSafeSubmits
                       renderOptions={this.renderOptions}
                       submitInput= {this.submitInput}
                       cancelInput={this.cancelInput}
                    />
                )
            }}
        />);
    };
    getInputInfo = (row) => {
        return {
            'id': row.id,
            'columnKey': this.props.columnKey
        };
    }
    onFocus = () => {
        let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
        this.props.actions.focusInput(
            this.getInputInfo(row)
        );

        //todo remove the previous onFocus from the nameACtions
    };
    onBlur = () => {
        this.props.actions.blurInput();
    };
    changeName = (e) => {
        let row = stateUtility.getRowData(this.props.products, this.props.rowIndex);
        this.props.actions.changeName(e.target.value, row.id);
    };
    render = () => {
        const {products, rowIndex, name, distanceFromLeftSideOfTableToStartOfCell, width} = this.props;
        let row = stateUtility.getRowData(products, rowIndex);
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

        let productName = name.names.byProductId[row.id];
        let isEditing = productName.originalValue !== productName.value;
        let nameValue = this.getProductName(row, productName);
        let Submits = this.createSubmits({rowIndex, distanceFromLeftSideOfTableToStartOfCell, width, isEditing, row});

        return (
            <TextAreaContainer>
                <TextArea
                    key={row.id+'-'+this.props.columnKey}
                    cols={COLS}
                    rows={2}
                    onFocus={this.onFocus}
                    onBlur={this.onBlur}
                    value={nameValue}
                    onChange={this.changeName}
                    data-inputinfo={JSON.stringify(this.getInputInfo(row),null,1)}
                />
                {Submits}
            </TextAreaContainer>
        )
    };
}

export default NameCell;