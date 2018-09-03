define([
    'react',
    'styled-components'
], function(
    React,
    styled
) {
    "use strict";
    
    styled = styled.default;
    
    let PageLink = styled.a.attrs({
        title: props => {
            return 'go to ' + props.count;
        }
    })`
        color: ${props => props.isCurrentPage ? 'blue' : ''};
        cursor:pointer;
        margin-left:1rem;
        margin-right:1rem;
    `;
    
    return PageLink;
});
