import React from 'react';
import {Link} from 'react-router-dom';
import styled from 'styled-components';

const StyledLink = styled(Link)`
    -webkit-font-smoothing: antialiased;
    color: #222 !important;
    font-family: Lato, Helvetica, Arial, sans-serif;
    font-size: 100%;
    margin: 0;
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
`;

const ButtonLink = (props) => {
    return (
        <StyledLink to={props.to}>{props.text}</StyledLink>
    )
}

export default ButtonLink;