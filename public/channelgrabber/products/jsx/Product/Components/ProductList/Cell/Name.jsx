import React from 'react';
import Clipboard from 'Clipboard';
import stateUtility from 'Product/Components/ProductList/stateUtility';
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

//border: ${props => props.active ? '3px dashed grey' : 'inherit'};

const COLS = 32;

class NameCell extends React.Component {
    static defaultProps = {};

    baseValue = 'Please write an essay about your favorite DOM element something else is in here somethingsomething else is in here somethingsomething else is in here something';

    state = {
        value: this.baseValue,
        shortenedValue : this.baseValue.substring(0, 40) + "..."
    };

    getVariationAttributeArray = (row) => {
        return Object.keys(row.attributeValues).map((key) => {
            return key + ': ' + row.attributeValues[key];
        });
    };
    getVariationName = (row) => {
        const isVariation = stateUtility.isVariation(row);
        if (isVariation) {
            let variationAttributeArray = this.getVariationAttributeArray(row);
            return variationAttributeArray.join(', ');
        }
        let {name} = this.props;
        let productName = name.names.byProductId[row.id];

        if(name.focusedId === row.id){
            return productName.value;
        }
        return productName.shortenedValue;
    };
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
    onFocus() {
        if (!this.state.isFocused) {
            this.setState({
                isFocused: true
            })
        }
    }
    onBlur() {
        if (this.state.isFocused) {
            this.setState({
                isFocused: false
            })
        }
    }
    render() {
        const {products, rowIndex, actions, name} = this.props;
        const row = stateUtility.getRowData(products, rowIndex);

        //todo - remove this hack....
//        return (
//            <SafeInputStateless
//                borderless={true}
//                width={200}
//            />
//        )
        let value = this.state.isFocused ? this.state.value : this.state.shortenedValue;

        return (
            <TextAreaContainer>
                <TextArea
                    cols={COLS}
                    rows={2}
                    onFocus={actions.focusName.bind(this, row.id)}
                    onBlur={actions.blurName.bind(this, row.id)}
                    value={this.getVariationName(row)}
                    onChange={actions.changeName.bind(this, row.id)}
                    active={this.isActive()}
                />
            </TextAreaContainer>
        )
//        return (
//            <NameContainer>
//                <LinesEllipsis
//                    text={name}
//                    maxLine='2'
//                    ellipsis='...'
//                    trimRight
//                    basedOn='letters'
//                    title={name}
//                />
//            </NameContainer>
//        );
    }
}

export default NameCell;