import React from 'react';
import {Link} from 'react-router-dom';
import styled from 'styled-components';

const ButtonLink = styled(Link)`
    border: 1px solid #333;
    padding: 10px;
    color: #333;
    display: inline-block;
`;

function createMarkup(raw) {
    return {__html: raw};
}

const MessageDetail = (props) => {
    const {match} = props;
    const {params} = match;
    const threadId = params.threadId.replace(':','');
    const thread = props.threads.byId[threadId];
    const totalMessageCount = props.threads.allIds.length;

    const thisThreadPosition = props.threads.allIds.indexOf(`${threadId}`);

    const prevThreadId = props.threads.allIds[thisThreadPosition - 1];
    const nextThreadId = props.threads.allIds[thisThreadPosition + 1];

    const prevThreadPath = thisThreadPosition !== 0 ? `/messages/thread/:${prevThreadId}` : `/messages/`;
    const nextThreadPath = thisThreadPosition !== totalMessageCount? `/messages/thread/:${nextThreadId}` : `/messages/`;

    console.log('thread', thread);

    return (
        <div>
            <ButtonLink to={`/messages/`}>
                back button to close thread and go back to messagelist view
            </ButtonLink>

            <h1 className='u-clear-both u-float-none'>{thread.subject}</h1>

            <ButtonLink to={prevThreadPath}>
                previous button to navigate to previous thread
            </ButtonLink>

            <p>{thisThreadPosition + 1} / {totalMessageCount}</p>

            <ButtonLink to={nextThreadPath}>
                next button to navigate to next thread
            </ButtonLink>

            <h2 className='u-clear-both u-float-none'>Last message content</h2>

            <div className='u-clear-both' dangerouslySetInnerHTML={createMarkup(thread.lastMessage)} />

            <h2 className='u-clear-both u-float-none'>Messages in thread</h2>

            <ol>
                {thread.messages.map((value, index) => {
                    return <li key={index}>{value}</li>
                })}
            </ol>

            <h2 className='u-clear-both u-float-none'>Right hand actions</h2>

            <ButtonLink to={thread.ordersLink}>
                {thread.ordersCount} Orders from {thread.externalUsername}
            </ButtonLink>

        </div>
    );
};

export default MessageDetail;
