import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';

const Index = (mountingNode, props) => {
    return ReactDOM.render(
        <App
            {...props}
        />,
        mountingNode
    );
};

export default Index;