import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components';

const Image = styled.img`
    max-width: ${props => props.maxLength}px;
    max-height: ${props => props.maxLength}px;
    object-fit: contain;
    background-color: #ebebeb;
    visibility: ${props=>props.imageLoaded ? 'visible' : 'hidden'};
`;
class ImageCell extends React.Component {
    static defaultProps = {};

    state = {
        error:false,
        imageLoaded:false
    };

    onError = () => {
        this.setState({
            error:true
        });
    };
    
    onLoad = () => {
        this.setState({
            imageLoaded:true
        });
    };
 
    renderImage = () => {
        let cellData = stateUtility.getCellData(
            this.props.products,
            this.props.columnKey,
            this.props.rowIndex
        );
       
        if (!cellData || !cellData.id || this.state.error ) {
            return '';
        }
        return (
            <Image
                title={'image-' + cellData.id}
                src={cellData.url}
                onError={this.onError}
                onLoad={this.onLoad}
                maxLength={this.props.height}
                imageLoaded={this.state.imageLoaded}
            />
        );
    };
    
    render() {
        return (
            <div {...this.props}>
                {this.renderImage()}
            </div>
        );
    }
}

export default ImageCell;

