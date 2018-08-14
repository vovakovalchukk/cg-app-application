define([
    'react',
    'fixed-data-table',
    'Product/Components/ProductList/stateFilters',
    'styled-components'
], function(
    React,
    FixedDataTable,
    stateFilters,
    styled
) {
    "use strict";

    styled = styled.default;

    let Image = styled.img`
        max-width: ${props =>  props.width}px;
        max-height: ${props => props.height}px;
        object-fit:contain;
    `;
 
    
    let TextCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {};
        },
        renderImage: function(){
            let cellData = stateFilters.getCellData(
                this.props.products,
                this.props.columnKey,
                this.props.rowIndex
            );
            if(!cellData || !cellData.id){
                return '';
            }
            return (
                <Image
                    title={'image-'+cellData.id}
                    src={cellData.url}
                    width={this.props.width}
                    height={this.props.height}
                />
            );
        },
        render() {
            return (
                <div {...this.props}>
                    {this.renderImage()}
                </div>
            );
        }
    });
    
    return TextCell;
});
