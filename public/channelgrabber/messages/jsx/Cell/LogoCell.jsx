import React from 'react';
import {Link} from 'react-router-dom';

const LogoCell = (props) => {
    let {rowData, column} = props;
    const value = rowData[column.key] || null;
    return (
        <Link to={`/messages/thread/:${props.rowData.id}`}>
            <div className={`u-margin-center sprite-messages-${value}-36`}></div>
        </Link>
    );
};

export default LogoCell;