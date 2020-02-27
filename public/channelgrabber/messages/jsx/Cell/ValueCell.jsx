import React from 'react';
import {Link} from 'react-router-dom';
import styled from 'styled-components';

const StyledLink = styled(Link)`
    color: #222222 !important;
`;

const getHoverText = (key, rowData) => {
    let title = null;
    switch (key) {
        case 'updatedFuzzy':
            title = rowData.updated;
            break;
        case 'createdFuzzy':
            title = rowData.created;
            break;
        default:
            break;
    }
    return title;
}

const ValueCell = (props) => {
    let {rowData, column} = props;
    const value = rowData[column.key] || null;
    const title = getHoverText(column.key, rowData);
    return (
        <StyledLink to={`/messages/thread/:${rowData.id}`}>
            <div title={title}>{value || '-'}</div>
        </StyledLink>
    );
};

export default ValueCell;