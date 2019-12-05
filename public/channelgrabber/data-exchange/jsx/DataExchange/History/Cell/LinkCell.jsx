import React from 'react';
import DownloadIcon from 'Common/Components/DownloadIcon';
import FlexContainer from 'Common/Components/FlexContainer';
import styled from 'styled-components';

const StyledDownloadIcon = styled(DownloadIcon)`
  font-size: 2em;
`;

const FlexChild = styled.span`
  margin: 5px;
`;

const LinkCell = (props) => {
    let {rowData, column} = props;

    const value = typeof column.getValue === 'function' ?
        column.getValue(rowData) : rowData[column.key] || null;
    
    const link = typeof column.getLink === 'function' ?
        column.getLink(rowData) : '';

    return (<FlexContainer>
        {value && <FlexChild>{value}</FlexChild>}
        {link && <FlexChild>
            <a href={link}>
                <StyledDownloadIcon />
            </a>
        </FlexChild>}
    </FlexContainer>);
};

export default LinkCell;