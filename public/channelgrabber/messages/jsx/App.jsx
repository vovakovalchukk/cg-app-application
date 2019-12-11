import React, { useEffect } from 'react';

const App = (props) => {
    console.log(props);

    useEffect(() => {
        props.actions.fetchFilters();
    }, []);

    return (
        <div className="u-width-100pc u-display-flex">
            <div id="Sidebar" className="u-flex-1">sidebar</div>
            <div id="Main" className="u-flex-5">main</div>
        </div>
    );

};

export default App;