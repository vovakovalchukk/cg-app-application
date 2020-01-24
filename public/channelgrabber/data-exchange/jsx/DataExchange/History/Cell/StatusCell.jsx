import React from 'react';
import FlexContainer from 'Common/Components/FlexContainer';
import styled from 'styled-components';
import TooltipComponent from "Product/Components/Tooltip";

const FlexChild = styled.span`
    margin: 5px;
    text-transform: capitalize;
`;

const InfoIcon = styled.span`
    cursor: pointer;
    font-size: 1.5em;
`;

const HoverContent = styled.span`
    white-space: pre-line;
`;

const STATUS_ERROR = 'error';
const STATUS_SUCCESS = 'success';

const StatusCell = (props) => {
    let {rowData, column} = props;

    const status = buildStatusFromRowData(rowData);

    const renderStatus = () => {
        if (!status) {
            return null;
        }

        return <FlexChild>{status}</FlexChild>;
    };

    const renderTooltip = () => {
        if (!status || !status.status === STATUS_ERROR || !rowData.reason) {
            return null;
        }

        return <TooltipComponent hoverContent={renderTooltipHoverContent()}>
            <InfoIcon>
                <i
                    className={"fa fa-info-circle"}
                    aria-hidden="true"
                />
            </InfoIcon>
        </TooltipComponent>
    };

    const renderTooltipHoverContent = () => {
        return <HoverContent>
            {rowData.reason}
        </HoverContent>
    };

    return <FlexContainer>
        {renderStatus()}
        {renderTooltip()}
    </FlexContainer>
};

export default StatusCell;

function buildStatusFromRowData(rowData) {
    if (rowData.status) {
        return rowData.status;
    }

    if (rowData.endDate === 'In Progress') {
        return rowData.endDate;
    }

    if (rowData.endDate) {
        return STATUS_SUCCESS;
    }

    return null;
}