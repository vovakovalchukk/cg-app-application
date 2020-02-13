import React from 'react';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import styled from 'styled-components';

const StyledDiv = styled.div`
    align-items: center;
    flex-shrink: 0;
`;

const getButtonSprite = (path, direction) => {
    return path === '/messages/' ? `sprite-arrow-${direction}-16-grey` : `sprite-arrow-${direction}-16-blue`;
}

const ThreadNavigator = (props) => {
    const {prev, next, thread, of} = props;

    const prevSprite = getButtonSprite(prev, 'left');

    const nextSprite = getButtonSprite(next, 'right');

    return (
        <StyledDiv className={`u-display-flex u-margin-left-small`}>
            <ButtonLink
                to={prev}
                sprite={prevSprite}
                title={`Previous thread`}
            />

            <div className={`u-margin-left-small u-margin-right-small`}>{thread} of {of}</div>

            <ButtonLink
                to={next}
                sprite={nextSprite}
                title={`Next thread`}
            />
        </StyledDiv>
    );
};

export default ThreadNavigator;