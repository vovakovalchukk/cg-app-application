define([
    'react',
    'fixed-data-table',
    'styled-components',
    'Product/Components/ProductList/stateUtility',
    'Product/Components/StockModeInputs'
], function(
    React,
    FixedDataTable,
    styled,
    stateUtility,
    StockModeInputs
) {
    "use strict";
    
    styled = styled.default;
    
    const StyledStockModeInputs = styled(StockModeInputs)`
        display:flex;
        justify-content:center;
        align-items:center;
    `;
    
    let StockModeCell = React.createClass({
        getDefaultProps: function() {
            return {
                products: {},
                rowIndex: null,
                stock: {}
            };
        },
        getInitialState: function() {
            return {
                editable: false,
                stockModeOption: {
                    name: '',
                    value: ''
                },
                stockAmount: ''
            };
        },
        submitInput: function() {
            if (!this.state.editable) {
                return;
            }
            
            //todo hit redux promise and set editable to be false if successful
            
            // var promise = this.props.submitCallback(this.props.name, this.state.newValue || 0);
            // promise.then(function(data) {
            //     this.setState({
            //         editable: false,
            //         newValue: data.savedValue
            //     });
            // }.bind(this));
            // promise.catch(function(error) {
            //     console.log(error.message);
            // });
        },
        onStockModeTypeChange: function(e) {
            this.setState({
                stockModeOption: e
            })
        },
        onStockAmountChange: function(e) {
            this.setState({
                stockAmount: e.target.value
            })
        },
        editInput: function() {
            console.log('in edit input');
            
            
            this.setState({
                editable: true
            })
        },
        render() {
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            
            const isSimpleProduct = stateUtility.isSimpleProduct(row);
            const isVariation = stateUtility.isVariation(row);
            
            if (!isSimpleProduct && !isVariation) {
                //todo - remove the text here before submission
                return <span></span>
            }
            
            //todo - change the input values to reflect what is coming back from the store
            return (
                <div>
                    <StyledStockModeInputs
                        onChange={this.onStockModeChange}
                        stockModeOptions={this.props.stock.stockModeOptions}
                        stockModeType={{
                            input: {
                                value: this.state.stockModeOption,
                                onChange: this.onStockModeTypeChange
                            }
                        }}
                        stockAmount={{
                            input: {
                                value: this.state.stockAmount,
                                onChange: this.onStockAmountChange
                            }
                        }}
                        onFocusMethod={this.editInput}
                    />
                    <div className={"safe-input-box"}>
                        <div className={"submit-input"}>
                            <div className={"submit-cancel " + (this.state.editable ? "active" : "")}>
                                <div className="button-input" onClick={this.submitInput}><span
                                    className="submit"></span></div>
                                <div className="button-input" onClick={this.cancelInput}><span
                                    className="cancel"></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }
    });
    
    return StockModeCell;
});
