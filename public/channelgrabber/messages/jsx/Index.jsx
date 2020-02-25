import React from 'react';
import ReactDOM from 'react-dom';
import Provider from 'MessageCentre/Provider';

const Index = (mountingNode, props) => {
    return ReactDOM.render(
        <Provider
            {...props}
        />,
        mountingNode
    );
};

export default Index;