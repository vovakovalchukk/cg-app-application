import PropTypes from 'prop-types';
import React from 'react';
import Tooltip from 'Product/Components/Tooltip';
import constants from 'Product/Components/ProductList/Config/constants';
import Skeleton from 'react-skeleton-loader';
import styled from 'styled-components';

"use strict";

const {LINK_STATUSES} = constants;
const LINK_ICON_DIMENSIONS = {
    width: 18,
    height: 18
};

const LinkIcon = styled.div`
    cursor: pointer;
    transform: scale(0.7);
`;

class LinkComponent extends React.Component {
    static defaultProps = {
        sku: "",
        productLinks: [],
        linkStatus: ''
    };

    state = {
        fetchingLinks: false,
    };

    onClick = () => {
        window.triggerEvent('productLinkEditClicked', {sku: this.props.sku, productLinks: this.props.productLinks});
    };

    onLinkRowClick = (sku) => {
        window.triggerEvent('getProductsBySku', {sku: [sku]});
    };

    getHoverContent = () => {
        if (this.props.productLinks.length === 0) {
            return (
                <div className="hover-link-none-msg">
                    <span>No linked products</span>
                </div>
            );
        }
        
        return this.props.productLinks.map(function(linkedProduct) {
            return (
                <div key={linkedProduct.sku}
                     className="product-link hover-link-row"
                     onClick={this.onLinkRowClick.bind(this, linkedProduct.sku)}
                     title="Click to search for this product."
                >
                        <span className="thumbnail">
                            <img
                                src={linkedProduct.product ? this.context.imageUtils.getProductImage(linkedProduct.product, linkedProduct.sku) : ''}/>
                        </span>
                    <span className="sku">{linkedProduct.sku}</span>
                    <span className="stock">{linkedProduct.quantity}</span>
                </div>
            );
        }.bind(this));
    };
  
    getLinkIcon = () => {
        if (this.props.linkStatus == LINK_STATUSES.fetching) {
            return (
                <Skeleton
                    width={LINK_ICON_DIMENSIONS.height + 'px'}
                    height={LINK_ICON_DIMENSIONS.height + 'px'}
                    borderRadius={(LINK_ICON_DIMENSIONS.height / 2) + 'px'}
                />
            );
        }
        var spriteClass = (this.props.productLinks.length ? 'sprite-linked-22-blue' : 'sprite-linked-22-white');
        return (
            <LinkIcon className={"sprite click " + spriteClass} onClick={this.onClick} />
        );
    };

    render() {
        return <Tooltip hoverContent={this.getHoverContent()}>
            {this.getLinkIcon()}
        </Tooltip>;
    }
}

LinkComponent.contextTypes = {
    imageUtils: PropTypes.object
};

export default LinkComponent;