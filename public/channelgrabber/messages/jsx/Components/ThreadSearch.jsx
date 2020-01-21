import React from 'react';
import { connect } from 'react-redux';
import Search from 'Common/Components/Search';
import styled from 'styled-components';

const mapStateToProps = state => {
    return {
        query: state.search.query,
    }
};

const StyledDiv = styled.div`
    border: 1px solid #c2c2c2;
    display: inline-flex;
    border-radius: 8px;
    padding: 2px;
    
    div {
        flex-direction: row-reverse;
    }
    
    button {
        padding: 0 3px;
    }
`;

const ThreadSearch = (props) => {
    const {actions, query} = props;

    return (
        <StyledDiv>
            <Search
                value={query}
                onChange={actions.searchInputType}
                onSearch={actions.searchSubmit}
            />
        </StyledDiv>
    );
};

export default connect(mapStateToProps)(ThreadSearch);
