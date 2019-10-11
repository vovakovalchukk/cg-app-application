import React from 'react';
import ReactDOM from 'react-dom';
import Table from "./Components/Table";

const App = (mountingNode, props) => {
    return ReactDOM.render(
        <Table
            {...props}
        />,
        mountingNode
    );
};

export default App;