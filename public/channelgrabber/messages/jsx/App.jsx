import React, { useEffect } from 'react';

const App = (props) => {
    useEffect(() => {
        props.actions.fetchFilters();
    }, []);

    return (
        <div className="u-width-100pc u-display-flex">
            <div id="Sidebar" className="u-flex-1">
                <h1 className="u-width-100pc">sidebar</h1>
                <ol className="u-padding-none">
                    { isSingleUser() && <li>unassigned <span>{getFilterCount('unassigned')}</span></li> }
                    { isSingleUser() && <li>assigned <span>{getFilterCount('assigned')}</span></li> }
                    { isSingleUser() && <li>myMessages <span>{getFilterCount('myMessages')}</span></li> }
                    <li>resolved <span>{getFilterCount('resolved')}</span></li>
                    <li>Open count {getOpenCount()}</li>
                </ol>
            </div>
            <div id="Main" className="u-flex-5">
                <h1>main</h1>
            </div>
        </div>
    );

    function isSingleUser () {
        return Object.keys(props.assignableUsers).length > 1;
    };

    function getFilterCount(id){
        if ( !props.filters.filters.byId[id] ) return null;
        return props.filters.filters.byId[id].count.toString();
    };

    function getOpenCount () {
        return (Number(getFilterCount('myMessages')) +
            Number(getFilterCount('unassigned')) +
            Number(getFilterCount('assigned'))).toString();
    }
};

export default App;