import React, {useEffect} from 'react';
import Table from 'Common/Components/Table';
import styled from 'styled-components';
import allColumns from 'MessageCentre/Columns/allColumns';
import Search from 'Common/Components/Search';

const Th = styled.th`
    position: sticky;
    top: 50px;
`;

const MessageList = (props) => {
    const {match} = props;
    const {params} = match;

    useEffect(() => {
        const filterObjectForAjax = {};
        const filterInState = props.filters.getById(params.activeFilter);

        if (!filterInState) {
            // filters have not yet been fetched - this will be when the view has initially rendered
            filterObjectForAjax[props.filters.default] = props.filters.default;
            props.actions.fetchMessages({
                filter: filterObjectForAjax
            });
            return null;
        }

        // fire the ajax request corresponding to react-router parameter on view load.
        filterObjectForAjax[filterInState.ajaxFilterProperty] = filterInState.ajaxFilterValue;

        props.actions.fetchMessages({
            filter: filterObjectForAjax
        });
    }, [props.filters]);

    return (
        <div>
            <Search
                value={props.searchValue}
                onSearch={(searchValue => {})}
            />

            <Table
                actions={props.actions}
                data={props.formattedThreads}
                maxItems={100}
                pagination={1}
                onPageChange={(newPage)=>{
                   // console.log('onPageChange')
                }}
                setRowValue={[]}
                columns={allColumns}
                maxPages={1}
                styledComponents={{
                    Th
                }}
            />
        </div>
    );
};

export default MessageList;
