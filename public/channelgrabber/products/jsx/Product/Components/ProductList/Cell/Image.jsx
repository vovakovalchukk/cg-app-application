import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components';
import styleVars from "../styleVars";

const Image = styled.img`
    max-width: ${props => props.width}px;
    max-height: ${props => props.height}px;
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
                width={this.props.width}
                height={this.props.height}
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

