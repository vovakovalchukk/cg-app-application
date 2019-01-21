import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components';

const ImageContainer = styled.div`
    height:100%;
    width:100%;
    display: flex;
    justify-content: center;
    padding-left: 0;
    padding-right: 0;
`;

const Image = styled.img`
    max-height: 100%;
    max-width: 100%;
    background-color: #ebebeb;
    visibility: ${props => props.imageLoaded ? 'visible' : 'hidden'};
`;

class ImageCell extends React.Component {
    static defaultProps = {
        height: 0
    };

    state = {
        error: false,
        imageLoaded: false
    };

    onError = () => {
        this.setState({
            error: true
        });
    };
    onLoad = () => {
        this.setState({
            imageLoaded: true
        });
    };
    renderImage = () => {
        if (!this.props.products.visibleRows[this.props.rowIndex]) {
            return;
        }

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
                onLoad={this.onLoad}
                maxLength={this.props.height}
                imageLoaded={this.state.imageLoaded}
            />
        );
    };
    render() {
        return (
            <ImageContainer {...this.props}>
                {this.renderImage()}
            </ImageContainer>
        );
    }
}

export default ImageCell;