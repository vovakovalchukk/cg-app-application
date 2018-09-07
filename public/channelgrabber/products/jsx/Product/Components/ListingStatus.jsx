define([
    'react',
    'styled-components'
], function(
    React,
    styled
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
    `;
    
    var ListingStatusComponent = React.createClass({
        getDefaultProps: function() {
            return {
                status: ''
            };
        },
        render: function() {
            return (
                <ListingIcon className={"listing-status " + this.props.status} {...this.props}/>
            );
        }
    });
    
    return ListingStatusComponent;
    
    function getBackgroundImage(props) {
        const IMAGE_DIR = 'cg-built/products/img/';
        let statusBackgroundMap = {
            active: IMAGE_DIR + 'listing-active.png',
            pending: IMAGE_DIR + 'listing-pending.png',
            paused: IMAGE_DIR + 'listing-paused.png',
            error: IMAGE_DIR + 'listing-error.png'
        };
        if (!statusBackgroundMap[props.status]) {
            return '../img/listing-unknown.png';
        }
        let backgroundReturned = statusBackgroundMap[props.status];
        
        return backgroundReturned;
    }
});
