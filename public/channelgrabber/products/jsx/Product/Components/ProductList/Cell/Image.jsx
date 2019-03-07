import React from 'react';
import stateUtility from 'Product/Components/ProductList/stateUtility';
import styled from 'styled-components';
import Tooltip from 'Product/Components/Tooltip';

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
    height: ${props => props.height ? props.height+'px' : 'auto'};
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
    getHoverContent = (cellData) => {
      return (
          <Image
              title={'image-' + cellData.id + '-hovered'}
              src={cellData.url}
              onError={this.onError}
              onLoad={this.onLoad}
              height={100}
              imageLoaded={this.state.imageLoaded}
          />
      )
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
            <Tooltip hoverContent={this.getHoverContent(cellData)}>
                <Image
                    title={'image-' + cellData.id}
                    src={cellData.url}
                    onError={this.onError}
                    onLoad={this.onLoad}
                    imageLoaded={this.state.imageLoaded}
                />
            </Tooltip>
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