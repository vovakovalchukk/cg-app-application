define([
    'react',
    'styled-components'
], function(
    React,
    styled
) {
    "use strict";
    
    styled = styled.default;
    
    let IconComponent = styled.span`
        width: 38px;
        height: 38px;
        display: inline-block;
        overflow: hidden;
        margin: -5px auto;
        vertical-align: middle;
        background-size: auto;
        background-repeat: no-repeat;
        background-position: center;
        cursor: pointer;
    }}`;
    
    return IconComponent;
});
