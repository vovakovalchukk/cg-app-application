import React from 'react';
import {Link} from 'react-router-dom';
import styled from 'styled-components';

const StyledLink = styled(Link)`
    color: #222222 !important;
`;

const ValueCell = (props) => {
    let {rowData, column} = props;
    const value = rowData[column.key] || null;
    return (
        <StyledLink to={`/messages/thread/:${props.rowData.id}`}>
            <div>{value}</div>
        </StyledLink>
    );
};

export default ValueCell;