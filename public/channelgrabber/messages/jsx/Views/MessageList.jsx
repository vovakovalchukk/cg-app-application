import React, {useEffect} from 'react';
import Table from 'Common/Components/Table';
import styled from 'styled-components';
import allColumns from 'MessageCentre/Columns/allColumns';
import Search from 'Common/Components/Search';
import Link from 'react-router-dom';

const TableRow = (props) => {
    console.log('in tableorw');
    return (

            <tr>
                    {props.children}

            </tr>
    );
    // return (
    //     <Link to={`/messages/thread/:${props.rowId}`}>
    //         <tr>
    //             {props.children}
    //         </tr>
    //     </Link>
    // );

};

const Th = styled.th`
    position: sticky;
    top: 50px;
    width: ${props => props.width || 'auto'};
`;

const Tr = styled(TableRow)`
    overflow: hidden;
    white-space: nowrap;
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
    }, [props.filters, props.match.params.activeFilter]);

    return (
        <div>
            <Search
                value={props.threads.searchBy}
                onChange={props.actions.searchInputType}
                onSearch={props.actions.searchSubmit}
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
                Th,
                Tr
            }}
            />
        </div>
    );
};

export default MessageList;