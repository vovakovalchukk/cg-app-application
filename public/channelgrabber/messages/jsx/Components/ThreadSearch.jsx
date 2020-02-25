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
    background-color: #fff;
    
    div {
        flex-direction: row-reverse;
    }
    
    button {
        padding: 0 4px;
    }
    
    input {
        border: 1px solid #c2c2c2;
        border-radius: 5px;
        font-size: 12px;
        padding: 5px;
    }
`;

const ThreadSearch = (props) => {
    const {actions, query} = props;

    return (
        <StyledDiv
            className={props.className}
        >
            <Search
                value={query}
                onChange={actions.searchInputType}
                onSearch={actions.searchSubmit}
                placeholder={'Search for...'}
            />
        </StyledDiv>
    );
};

export default connect(mapStateToProps)(ThreadSearch);
