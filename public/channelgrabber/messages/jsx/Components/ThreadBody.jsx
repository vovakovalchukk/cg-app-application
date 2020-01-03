import React from 'react';

function createMarkup(raw) {
    return {__html: raw};
}

const ThreadBody = (props) => {
    const {lastMessage} = props;
    return (
        <div
            className={`u-clear-both`}
            dangerouslySetInnerHTML={createMarkup(lastMessage)}
        />
    )
}

export default ThreadBody;
