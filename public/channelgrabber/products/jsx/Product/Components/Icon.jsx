import React from 'react';
import styled from 'styled-components';
"use strict";

let IconComponent = styled.span`
        width: 2rem;
        height: 2rem;
        display: inline-block;
        overflow: hidden;
        vertical-align: middle;
        background-size: auto;
        background-repeat: no-repeat;
        background-position: center;
        cursor: ${props => (props.cursor ? props.cursor : 'pointer')};
  `;

export default IconComponent;
