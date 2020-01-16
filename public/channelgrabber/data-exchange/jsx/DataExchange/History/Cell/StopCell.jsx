import React, {useState} from 'react';
import stopRequest from 'DataExchange/History/Service/stopRequest';

const StopCell = (props) => {
    let {rowData, setRowValue} = props;

    if (rowData.endDate !== 'In Progress') {
        return null;
    }

    if (rowData.status === 'Stopped') {
        return <div>Stopped</div>
    }

    return (<div>
        <button
            className={'button'}
            onClick={sendStopRequest}
        >
            Stop
        </button>
    </div>);

    function sendStopRequest() {
        stopRequest(rowData.id, setRowValue.bind(this, rowData.id, 'status', 'Stopped'))
    }
};

export default StopCell;