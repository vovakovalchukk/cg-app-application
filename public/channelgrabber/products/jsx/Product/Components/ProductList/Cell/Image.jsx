import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components';
    
    const Image = styled.img`
        max-width: ${props => props.width}px;
        max-height: ${props => props.height}px;
        object-fit:contain;
    `;
    
    let ImageCell = React.createClass({
        getDefaultProps: function() {
            return {};
        },
        getInitialState: function() {
            return {
                error:false
            };
        },
        onError:function(e){
            this.setState({
                error:true
            });
        },
        renderImage: function() {
            let cellData = stateUtility.getCellData(
                this.props.products,
                this.props.columnKey,
                this.props.rowIndex
            );
            if (!cellData || !cellData.id || this.state.error) {
                return '';
            }
            return (
                <Image
                    title={'image-' + cellData.id}
                    src={cellData.url}
                    onError={this.onError}
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
    
    export default ImageCell;

