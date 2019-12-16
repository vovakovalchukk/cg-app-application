import React from 'react';
import ReactDOM from 'react-dom';
import OrderExportApp from './App';

const Index = (mountingNode, props) => {
    return ReactDOM.render(
        <OrderExportApp
            {...props}
        />,
        mountingNode
    );
};

export default Index;
