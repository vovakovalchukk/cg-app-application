import React from 'react';
import styled from 'styled-components';
    
    let PageLink = styled.a.attrs({
        title: props => {
            return 'go to ' + props.count;
        }
    })`
        color: ${props => props.isCurrentPage ? '#1477aa' : ''};
        cursor:pointer;
        margin-left:1rem;
        margin-right:1rem;
    `;
    
    export default PageLink;