import React from 'react';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import ThreadNavigator from 'MessageCentre/Components/ThreadNavigator';
import styled from 'styled-components';

const FlexDiv = styled.div`
    justify-content: space-between;
    align-items: center;
`;

const ThreadHeader = (props) => {
    const {
        subject,
        prevThreadPath,
        nextThreadPath,
        threadPosition,
        totalThreadCount
    } = props;

    return(
        <FlexDiv className={`u-display-flex`}>
            <ButtonLink
                to={`/messages/`}
                text={`< Back`}
            />

            <h1 className='u-clear-both u-float-none'>{subject}</h1>

            <ThreadNavigator
                prev={prevThreadPath}
                next={nextThreadPath}
                thread={threadPosition}
                of={totalThreadCount}
            />
        </FlexDiv>
    )
};

export default ThreadHeader;
