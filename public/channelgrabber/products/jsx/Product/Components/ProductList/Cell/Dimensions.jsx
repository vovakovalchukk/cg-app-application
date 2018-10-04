import React from 'react';
import styled from 'styled-components';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import Input from 'Common/Components/SafeInput';
    
    const InputsContainer = styled.div`
        display:flex;
        justify-content:center;
        align-items:center;
    `;
    
    const StyledInput= styled(Input)`
        display:inline-block
    `;
    
    let DimensionsCell = React.createClass({
        getDefaultProps: function() {
            return {
                products: {},
                rowIndex: null
            };
        },
        getInitialState: function() {
            return {};
        },
        renderInput: function(row, detail) {
            return (
                <StyledInput
                    name={detail}
                    initialValue={(row.details && row.details[detail]) ? row.details[detail] : detail.substring(0,1)}
                    step="0.1"
                    submitCallback={this.props.actions.saveDetail.bind(this, row)}
                />
            )
        },
        render: function() {
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            
            const isSimpleProduct = stateUtility.isSimpleProduct(row)
            const isVariation = stateUtility.isVariation(row);
            
            if (!isSimpleProduct && !isVariation) {
                return <span></span>
            }
            
            return (
                <InputsContainer className={this.props.className}>
                    {this.renderInput(row, 'height')} x
                    {this.renderInput(row, 'width')} x
                    {this.renderInput(row, 'length')}
                </InputsContainer>
            );
        }
    });
    
    export default DimensionsCell;

