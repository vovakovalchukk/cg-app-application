import React from 'react';
import ReactDOM from 'react-dom';
import Table from "../Schedule/Table";
import Service from "./Components/Service";

const App = (mountingNode, props) => {
    return ReactDOM.render(
        <Table
            {...props}
            buildEmptySchedule={Service.buildEmptySchedule}
            columns={Service.getColumns()}
            formatPostDataForSave={Service.formatPostDataForSave}
            validators={Service.validators()}
        />,
        mountingNode
    );
};

export default App;
