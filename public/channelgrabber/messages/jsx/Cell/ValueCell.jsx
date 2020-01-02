import React from 'react';
import {Link} from 'react-router-dom';

const ValueCell = (props) => {
    let {rowData, column} = props;
    const value = rowData[column.key] || null;
    return (
        <Link to={`/messages/thread/:${props.rowData.id}`}>
            <p>{value}</p>
        </Link>
    );
};

export default ValueCell;