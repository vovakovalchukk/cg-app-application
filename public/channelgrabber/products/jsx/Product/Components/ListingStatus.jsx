import React from 'react';
import styled from 'styled-components';
import constants from 'Product/Components/ProductList/Config/constants';
import Icon from 'Product/Components/Icon';

"use strict";

let ListingIcon = styled(Icon)`
    background-image: url('${getBackgroundImage}');
    background-size: 80%;
    cursor: ${props => (props.status === 'active' ? 'pointer' : 'inherit')};
    ${props => {
    if (props.status === 'inactive') {
        return `
                    &:hover{
                        background-image: url('${constants.ADD_ICON_URL}');
                        background-size: 40%;
                    }
                `;
    }
}}
`;

ListingIcon.sizer = styled.div`
    display: flex;
    width: ${constants.LISTING_ICON_SIZE + 'px'};
    height: ${constants.LISTING_ICON_SIZE + 'px'};
`;

class ListingStatusComponent extends React.Component {
    static defaultProps = {
        status: '',
        title: '',
        onAddListingClick: () => {
        },
        className: '',
        listingUrl: ''
    };

    getCursor() {
        return this.props.status === 'inactive' ? 'pointer' : 'initial';
    };
    getListingIconOnClick(e) {
        if (this.props.status !== 'inactive') {
            return;
        }
        e.preventDefault();
        this.props.onAddListingClick();
    }
    getListingHref() {
        if (this.props.status !== 'active') {
            return '';
        }
        return this.props.listingUrl
    }
    render() {
        return (
            <div className={this.props.className}>
                <a href={this.getListingHref()} target="_blank" onClick={this.getListingIconOnClick.bind(this)}>
                    <ListingIcon.sizer>
                        <ListingIcon
                            {...this.props}
                            cursor={this.getCursor()}
                            title={this.props.title}
                        />
                    </ListingIcon.sizer>
                </a>
            </div>
        );
    }
}

export default ListingStatusComponent;

function getBackgroundImage(props) {
    const IMAGE_DIR = constants.IMAGE_DIR;
    let statusBackgroundMap = {
        active: IMAGE_DIR + 'listing-active.png',
        pending: IMAGE_DIR + 'listing-pending.png',
        paused: IMAGE_DIR + 'listing-paused.png',
        error: IMAGE_DIR + 'listing-error.png',
        inactive: IMAGE_DIR + 'listing-unknown.png'
    };
    if (!statusBackgroundMap[props.status]) {
        return '../img/listing-unknown.png';
    }
    return statusBackgroundMap[props.status];
}