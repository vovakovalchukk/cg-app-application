import PropTypes from 'prop-types';
import React from 'react';
import Tooltip from 'Product/Components/Tooltip';


class LinkComponent extends React.Component {
    static defaultProps = {
        sku: "",
        productLinks: []
    };

    state = {
        fetchingLinks: false,
    };

    componentDidMount() {
        window.addEventListener('fetchingProductLinksStart', this.onStartFetchingLinks, false);
        window.addEventListener('fetchingProductLinksStop', this.onStopFetchingLinks, false);
    }

    componentWillUnmount() {
        window.removeEventListener('fetchingProductLinksStart', this.onStartFetchingLinks, false);
        window.removeEventListener('fetchingProductLinksStop', this.onStopFetchingLinks, false);
    }

    onStartFetchingLinks = () => {
        this.setState({ fetchingLinks: true });
    };

    onStopFetchingLinks = () => {
        this.setState({ fetchingLinks: false });
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
                        <img src={linkedProduct.product ? this.context.imageUtils.getProductImage(linkedProduct.product, linkedProduct.sku) : ''} />
                    </span>
                    <span className="sku">{linkedProduct.sku}</span>
                    <span className="stock">{linkedProduct.quantity}</span>
                </div>
            );
        }.bind(this));
    };

    getLinkIcon = () => {
        if (this.state.fetchingLinks) {
            return (
                <span>
                    <img
                        title="Loading Product Links..."
                        src="/channelgrabber/zf2-v4-ui/img/loading-transparent-21x21.gif"
                        className="b-loader"
                    />
                </span>
            );
        }
        var spriteClass = (this.props.productLinks.length ? 'sprite-linked-22-blue' : 'sprite-linked-22-white');
        return (
            <span className={"sprite "+ spriteClass + " click"}
                  onClick={this.onClick}
            ></span>
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
