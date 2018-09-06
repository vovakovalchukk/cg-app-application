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
        background-image: url('../img/listing-active.png');
    `;
    // background-image: url('${getBackgroundImage}');
    
    var ListingStatusComponent = React.createClass({
        getDefaultProps: function() {
            return {
                status:''
            };
        },
        render: function () {
            return (
                    <ListingIcon className={"listing-status " + this.props.status} {...this.props}/>
            );
        }
    });
    
    return ListingStatusComponent;
    
    function getBackgroundImage(props){
        console.log('in getBackgroundImage with props: ' , props);
        
        
        let statusBackgroundMap = {
            active:'../img/listing-active.png',
            pending:'../img/listing-pending.png',
            paused: '../img/listing-paused.png',
            error: '../img/listing-error.png'
        };
        if(!statusBackgroundMap[props.status]){
            return '../img/listing-unknown.png';
        }
        let backgroundReturned = statusBackgroundMap[props.status];
        
        console.log('backgroundReturned: ', backgroundReturned);
        
        
        return backgroundReturned;
    }
});
