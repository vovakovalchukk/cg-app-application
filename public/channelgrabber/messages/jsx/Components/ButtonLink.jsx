import React from 'react';
import {Link} from 'react-router-dom';
import styled from 'styled-components';

const StyledLink = styled(Link)`
    -webkit-font-smoothing: antialiased;
    color: #222 !important;
    font-family: Lato, Helvetica, Arial, sans-serif;
    font-size: 100%;
    vertical-align: baseline;
    line-height: normal;
    text-transform: none;
    -moz-appearance: button;
    -webkit-appearance: button;
    appearance: button;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    word-break: break-all;
`;

const ButtonLink = (props) => {
    const {className, sprite, to, text} = props;
    return (
        <StyledLink className={className} to={to}>
            {sprite ? <span className={sprite}></span> : text}
        </StyledLink>
    )
}

export default ButtonLink;