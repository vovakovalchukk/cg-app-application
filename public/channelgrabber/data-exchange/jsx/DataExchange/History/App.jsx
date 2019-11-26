import React, {useState, useCallback, useEffect} from 'react';
import historyFetch from './Service/historyFetch'
import Paginator from "Common/Components/Pagination/Paginator";

const HistoryApp = (props) => {
    const [data, setData] = useState({});
    const [pagination, setPagination] = useState(0);
    const [something, setSomething] = useState(0);

    useEffect(() => {
        historyFetch(pagination, setData);

    }, []);

    let maxPages = 4;

    return (<div>
        <div>pagi-{pagination}</div>
        <div>sth - {something}</div>
        <div>
            <Paginator
                incrementPage={()=>{setPagination(incrementToMax(pagination, maxPages))}}
                decrementPage={()=>{setPagination(decrementTo0(pagination))}}
                currentPage={pagination}
                //todo - fix this
                maxPages={maxPages}
            />
        </div>
    </div>);
};



export default HistoryApp;

function decrementTo0(number) {
    return number -1 >= 0 ? number - 1 : 0;
}

function incrementToMax(number, max) {
    return number + 1 <= max ? number + 1 : max;
}