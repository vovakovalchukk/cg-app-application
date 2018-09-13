define([
    'react',
    'styled-components',
    'Product/Components/ProductList/Config/constants'
], function(
    React,
    styled,
    constants
) {
    "use strict";
    
    styled = styled.default;
    
    let ListingIcon = styled.span`
        width: 38px;
        height: 38px;
        display: inline-block;
        overflow: hidden;
        margin: -5px auto;
        vertical-align: middle;
        background-image: url('${getBackgroundImage}');
        background-size:auto;
        background-repeat: no-repeat;
        background-position: center;
        cursor: pointer;

        ${props => {
            if (props.status === 'inactive') {
                return `
                    &:hover{
                        background-image: url('${constants.ADD_ICON_URL}');
                    }
                `;
            }
    }}`;
    
    let ListingStatusComponent = React.createClass({
        getDefaultProps: function() {
            return {
                status: ''
            };
        },
        onAddListingClick: async function() {
            const {products, rowIndex} = this.props;
            const rowData = this.getRowData(products, rowIndex);
            this.props.actions.createNewListing({
                rowData
            });
        },
        render: function() {
            return (
                <ListingIcon
                    className={"listing-status " + this.props.status}
                    onClick={this.props.status === 'inactive' ? this.onAddListingClick : () => {}}
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
