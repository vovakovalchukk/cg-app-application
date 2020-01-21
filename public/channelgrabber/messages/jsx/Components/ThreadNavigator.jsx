import React from 'react';
import ButtonLink from 'MessageCentre/Components/ButtonLink';
import styled from 'styled-components';

const StyledDiv = styled.div`
    align-items: center;
`

const ThreadNavigator = (props) => {
    const {prev, next, thread, of} = props;
    return (
        <StyledDiv className={`u-display-flex`}>
            <ButtonLink
                to={prev}
                sprite={`sprite-arrow-left-16-grey`}
                title={`Previous thread`}
            />

            <p className={`u-margin-none`}>{thread} / {of}</p>

            <ButtonLink
                to={next}
                sprite={`sprite-arrow-right-16-grey`}
                title={`Next thread`}
            />
        </StyledDiv>
    );
};

export default ThreadNavigator;