import React, {useEffect} from 'react';
import Table from 'Common/Components/Table';
import styled from 'styled-components';
import allColumns from 'MessageCentre/Columns/allColumns';
import ThreadSearch from 'MessageCentre/Components/ThreadSearch';

const TableRow = (props) => {
    return (
        <tr>
            {props.children}
        </tr>
    );
};

const Th = styled.th`
    position: sticky;
    top: 0;
    width: ${props => props.width || 'auto'};
`;

const Tr = styled(TableRow)`
    overflow: hidden;
    white-space: nowrap;
`;

const TableHolder = styled.div`
    margin-top: -1rem;
`;

const MessagesGridActions = styled.div`
    background-color: #f5f5f5;
    padding: 10px;
    grid-column: head/right;
    grid-row: head;
`;

const MessagesGridList = styled.div`
    grid-row: main/foot;
    grid-column: main/right;
    overflow: auto;
`;

const NoMessages = styled.div`
    padding: 2rem;
`;

const MessageList = (props) => {
    const {
        match,
        filters,
        actions,
        formattedThreads,
        threadsLoaded,
        filter,
        filterValue
    } = props;

    const {params} = match;

    useEffect(() => {
        const filterObjectForAjax = {};
        const filterInState = filters.getById(params.activeFilter);

        if (
            filter !== null &&
            filterValue !== null &&
            typeof filter !== 'undefined' &&
            typeof filterValue !== 'undefined'
        ) {
            filterObjectForAjax[filter] = filterValue;
        }

        if (!filterInState) {
            // filters have not yet been fetched - this will be when the view has initially rendered
            filterObjectForAjax[filters.default] = filters.default;
            actions.fetchMessages({
                filter: filterObjectForAjax
            });
            return;
        }

        if (!filterInState.ajaxFilterProperty || !filterInState.ajaxFilterValue) {
            return;
        }

        // fire the ajax request corresponding to react-router parameter on view load.
        filterObjectForAjax[filterInState.ajaxFilterProperty] = filterInState.ajaxFilterValue;

        actions.fetchMessages({
            filter: filterObjectForAjax
        });
    }, [filters, match.params.activeFilter]);

    const showNoMessagesMessage = threadsLoaded === true && formattedThreads.length === 0;

    return (
        <React.Fragment>
            <MessagesGridActions>
                <ThreadSearch
                    actions={actions}
                />
            </MessagesGridActions>
            <MessagesGridList>
                {
                    showNoMessagesMessage &&
                    <NoMessages>
                        <span className="heading-large u-margin-bottom-med">No matching messages</span>
                        Please try another filter or search term.
                    </NoMessages>
                }

                {
                    !showNoMessagesMessage &&
                    <TableHolder>
                        <Table
                            actions={actions}
                            data={formattedThreads}
                            maxItems={100}
                            pagination={1}
                            onPageChange={(newPage)=>{
                                // todo - need to test pagination works
                            }}
                            setRowValue={[]}
                            columns={allColumns}
                            maxPages={1}
                            styledComponents={{
                                Th,
                                Tr
                            }}
                        />
                    </TableHolder>
                }

            </MessagesGridList>
        </React.Fragment>
    );
};

export default MessageList;
