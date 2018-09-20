define([
    'react',
    'fixed-data-table',
    'styled-components',
    'Product/Components/ProductList/stateUtility',
    'Product/Components/StockModeInputs',
    'Product/Components/ProductList/Config/constants'
], function(
    React,
    FixedDataTable,
    styled,
    stateUtility,
    StockModeInputs,
    constants
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
        cancelInput: function() {
            //todo set oldValue to be props
            this.setState({
                editable: false,
            });
        },
        onStockModeTypeChange: function(stockMode) {
            // console.log('onStockModeTypeChange stockMode: ' , stockMode);
            const {products, rowIndex} = this.props;
            const row = stateUtility.getRowData(products, rowIndex);
            this.props.actions.changeStockMode(row, stockMode.value, 'stockMode');
        },
        onStockAmountChange: function(e) {
            // console.log('onStockModeAmountChange e: ',e);
            
            
            // this.setState({
            //     stockAmount: e.target.value
            // })
        },
        editInput: function() {
            // console.log('in edit input');
            
            
            this.setState({
                editable: true
            })
        },
        render() {
            const {products, rowIndex} = this.props;
            // console.log('StockMode render with this.props: ', this.props);
            
            const row = stateUtility.getRowData(products, rowIndex);
            // console.log('row: ', row);
            // console.log('in StockMode render this.props: ', this.props , ' row :  ' , row);
            const isSimpleProduct = stateUtility.isSimpleProduct(row);
            const isVariation = stateUtility.isVariation(row);
            
            let editStatus = getEditStatus(this.props.stock.stockModeEdits, row);
            const shouldDisplaySaveCancelBox = editStatus === constants.STOCK_MODE_EDITING_STATUSES.editing;
            
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
                                value: {
                                    value: this.state.stockMode ? this.state.stockMode.value : row.stock.stockMode
                                },
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
                            <div className={"submit-cancel " + (shouldDisplaySaveCancelBox ? "active" : "")}>
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
    
    function getEditStatus(stockModeEdits,row){
        let editStatus = '';
        if (stockModeEdits.length > 0) {
            let matchedEdit = stockModeEdits.find(edit => {
                console.log('in find ', {
                    edit, row
                });
                return edit.productId === row.id
            });
            if(matchedEdit){
                editStatus = matchedEdit.status;
            }
        }
        return editStatus
    }
});
