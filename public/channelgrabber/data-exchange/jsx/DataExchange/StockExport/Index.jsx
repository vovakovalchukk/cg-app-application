import React from 'react';
import ReactDOM from 'react-dom';

import App from './App';

const Index = (mountingNode, props) => {
    console.log('in Index sith props', props);



    return ReactDOM.render(
        <App
            {...props}
        />,
        mountingNode
    );
};

export default Index;