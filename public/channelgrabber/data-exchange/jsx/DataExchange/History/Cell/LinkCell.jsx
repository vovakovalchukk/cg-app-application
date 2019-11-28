import React from 'react';
import DownloadIcon from 'Common/Components/DownloadIcon';
import styled from 'styled-components';

const StyledDownloadIcon = styled(DownloadIcon)`
  font-size: 2em;
`;

const ContentFlex = styled.div`
  display: inline-flex;
  justify-content: space-between;
  align-items: center;
`;

const FlexChild = styled.span`
  margin: 5px;
`;

const LinkCell = (props) => {
    let {rowData, column} = props;

    let value = typeof column.getValue === 'function' ?
        column.getValue(rowData) : rowData[column.key] || null;
    
    let link = typeof column.getLink === 'function' ?
        column.getLink(rowData) : '';

    return (<ContentFlex>
        {value && <FlexChild>{value}</FlexChild>}
        {link && <FlexChild>
            <a href={link}>
                <StyledDownloadIcon />
            </a>
        </FlexChild>}
    </ContentFlex>);
};

export default LinkCell;