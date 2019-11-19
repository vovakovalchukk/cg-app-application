import React from 'react';
import Table from "../Schedule/Table";
import Service from "./Components/Service";

const OrderExportApp = (props) => {
    
    console.log('in OrderExportApp', props);

    
    return (<div>
            <Table
                {...props}
                buildEmptySchedule={Service.buildEmptySchedule}
                columns={Service.getColumns()}
                formatPostDataForSave={Service.formatPostDataForSave}
                validators={Service.validators()}
            />
    </div>);
};

export default OrderExportApp;
