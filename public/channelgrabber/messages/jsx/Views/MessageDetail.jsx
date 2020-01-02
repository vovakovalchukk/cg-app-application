import React from 'react';

const MessageDetail = (props) => {
    const {match} = props;
    const {params} = match;
    const threadId = params.threadId.replace(':','');
    const thread = props.threads.byId[threadId];

    return (
        <div>
            <h1>Thread subject:</h1>
            <p className='u-clear-both'>{thread.subject}</p>
        </div>
    );
};

export default MessageDetail;
