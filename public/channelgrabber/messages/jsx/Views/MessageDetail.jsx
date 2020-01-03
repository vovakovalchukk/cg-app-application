import React from 'react';
import styled from 'styled-components';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import ThreadHeader from 'MessageCentre/Components/ThreadHeader';

function createMarkup(raw) {
    return {__html: raw};
}

const FlexDiv = styled.div`
    justify-content: space-between;
`;

const GridDiv = styled.div`
    display: grid;
    grid-template-columns: 1fr 300px;
    grid-gap: 20px;
`;

const MessageLi = styled.li`
    overflow: auto;
    padding-bottom: 10px;
    border-bottom: 1px solid #333;
    margin-bottom: 10px;
`;

const StyledIframe = styled.iframe`
    border: none;
`;

const MessageDetail = (props) => {
    const {match, threads} = props;
    const {params} = match;
    const threadId = params.threadId.replace(':','');
    const thread = threads.byId[threadId];
    const totalThreadCount = threads.allIds.length;
    const thisThreadPosition = threads.allIds.indexOf(`${threadId}`);
    const prevThreadId = threads.allIds[thisThreadPosition - 1];
    const nextThreadId = threads.allIds[thisThreadPosition + 1]
    const prevThreadPath = thisThreadPosition !== 0 ? `/messages/thread/:${prevThreadId}` : `/messages/`;
    const nextThreadPath = thisThreadPosition !== totalThreadCount ? `/messages/thread/:${nextThreadId}` : `/messages/`;

    const headerProps = {...thread};
    headerProps.nextThreadPath = nextThreadPath;
    headerProps.prevThreadPath = prevThreadPath;
    headerProps.threadPosition = thisThreadPosition + 1;
    headerProps.totalThreadCount = totalThreadCount;

    const messages = [];
    thread.messages.forEach(messageId => {
        const message = props.messages.byId[messageId];
        messages.push(message);
    });

    const customerSprite = `sprite-message-customer-21-red`;
    const staffSprite = `sprite-message-staff-21-blue`;

    return (
        <GridDiv>
            <div>
                <ThreadHeader {...headerProps} />
                <ol>
                    {messages.map((message, index) => {
                        const spriteClass = message.personType === 'customer' ? customerSprite : staffSprite;
                        return (
                            <MessageLi key={message.id}>
                                <h2>Message {index + 1}</h2>
                                <FlexDiv className={`u-display-flex`}>
                                    <div>
                                        <div title={message.personType} className={spriteClass} />
                                        <p>{message.name}</p>
                                    </div>
                                    <div>
                                        <p>{message.created}</p>
                                        <p>todo: print link</p>
                                    </div>
                                </FlexDiv>
                                <StyledIframe
                                    width={`660`}
                                    height={`660`}
                                    srcDoc={message.body}
                                />
                            </MessageLi>
                        )
                    })}
                </ol>
            </div>
            <div>
                <ButtonLink
                    to={thread.ordersLink}
                    text={`${thread.ordersCount} Orders from ${thread.externalUsername}`}
                />
            </div>
        </GridDiv>
    );
};

export default MessageDetail;
