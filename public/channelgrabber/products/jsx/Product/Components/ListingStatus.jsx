define([
    'react',
    'styled-components',
    'Product/Components/ProductList/Config/constants',
    'Product/Components/Icon'
], function(
    React,
    styled,
    constants,
    Icon
) {
    "use strict";
    
    styled = styled.default;
    
    let ListingIcon = styled(Icon)`
        background-image: url('${getBackgroundImage}');
        ${props => {
            if (props.status === 'inactive') {
                return `
                    &:hover{
                        background-image: url('${constants.ADD_ICON_URL}');
                    }
                `;
            }
        }}
    `;
    
    let ListingStatusComponent = React.createClass({
        getDefaultProps: function() {
            return {
                status: ''
            };
        },
        render: function() {
            return (
                <ListingIcon
                    className={"listing-status " + this.props.status}
                    onClick={this.props.status === 'inactive' ? this.props.onAddListingClick : () => {}}
                    {...this.props}
                />
            );
        }
    });
    
    return ListingStatusComponent;
    
    function getBackgroundImage(props) {
        const IMAGE_DIR = constants.IMAGE_DIR;
        let statusBackgroundMap = {
            active: IMAGE_DIR + 'listing-active.png',
            pending: IMAGE_DIR + 'listing-pending.png',
            paused: IMAGE_DIR + 'listing-paused.png',
            error: IMAGE_DIR + 'listing-error.png',
            inactive: IMAGE_DIR + 'listing-unknown.png',
        };
        if (!statusBackgroundMap[props.status]) {
            return '../img/listing-unknown.png';
        }
        return statusBackgroundMap[props.status];
    }
});
