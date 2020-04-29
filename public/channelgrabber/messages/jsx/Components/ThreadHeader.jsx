import React from 'react';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import ThreadNavigator from 'MessageCentre/Components/ThreadNavigator';
import styled from 'styled-components';

const FlexDiv = styled.div`
    justify-content: space-between;
    align-items: flex-start;
`;

const getNavigatorProps = (threadIds, thread) => {
    const safeThreadId = thread.id.replace(':', '');
    const totalThreadCount = threadIds.length;
    const thisThreadPosition = threadIds.indexOf(safeThreadId);
    const prevThreadId = threadIds[thisThreadPosition - 1];
    const nextThreadId = threadIds[thisThreadPosition + 1];
    const prevThreadPath = thisThreadPosition !== 0 ? `/messages/thread/:${prevThreadId}` : `/messages/`;
    const nextThreadPath = thisThreadPosition + 1 !== totalThreadCount ? `/messages/thread/:${nextThreadId}` : `/messages/`;
    return {
        nextThreadPath: nextThreadPath,
        prevThreadPath: prevThreadPath,
        threadPosition: thisThreadPosition + 1,
        totalThreadCount: totalThreadCount,
    };
};

const ThreadHeader = (props) => {
    const {
        thread,
        threadIds,
    } = props;

    const navigatorProps = getNavigatorProps(threadIds, thread);

    const renderProductLink = (thread) => {
        if (typeof thread.productLink.url == 'undefined' || typeof thread.productLink.wrap == 'undefined' || !thread.subject.match(thread.productLink.wrap) ) {
            return thread.subject;
        }
        let parts = thread.subject.split(thread.productLink.wrap);
        let prefix = parts.shift();
        let suffix = parts.join(thread.productLink.wrap);
        return (
            <span>
                {prefix}
                <a href={thread.productLink.url} target="_blank">{thread.productLink.wrap}</a>
                {suffix}
            </span>
        )
    };

    return(
        <FlexDiv className={`u-display-flex`}>
            <ButtonLink
                to={`/messages/`}
                sprite={`sprite-arrow-double-14-black`}
            />

            <h1 className={'u-clear-both u-float-none u-margin-left-small'}>
                {typeof thread.productLink == 'undefined' ? thread.subject : renderProductLink(thread)}
            </h1>

            <ThreadNavigator
                prev={navigatorProps.prevThreadPath}
                next={navigatorProps.nextThreadPath}
                thread={navigatorProps.threadPosition}
                of={navigatorProps.totalThreadCount}
            />
        </FlexDiv>
    )
};

export default ThreadHeader;
