define([
    'react',
    'styled-components',
    'Common/Components/Select'
], function(
    React,
    styled,
    Select
) {
    "use strict";
    
    styled = styled.default;
    
    // const Link1 = ({className, children}) => (
    //     <a>
    //         {children}
    //     </a>
    //     // return (
    //     //     <select value={props.limit} onChange={e => {
    //     //         props.changeLimit(e.target.value);
    //     //     }}>
    //     //         {
    //     //             props.options.map((option) => {
    //     //                     return (<option value={option}>{option}</option>);
    //     //                 }
    //     //             )
    //     //         }
    //     //     </select>
    //     // )
    // );
    const Link1 = ({className, children}) => (
            <a className={className}>
                {children}
            </a>
    );
    const StyledLink1= styled(Link1)`
        color: palevioletred;
        font-weight: bold;
`;
    
    const Link2 = ({className, children}) => (
        <a className={className}>
            {children}
        </a>
    );
    const StyledLink2 = styled(Link2)`
        color: palevioletred;
        font-weight: bold;
`;

//
//     const Link = ({ className, children }) => (
//         <a className={className}>
//             {children}
//         </a>
//     )
//
//     const StyledLink = styled(Link)`
//   color: palevioletred;
//   font-weight: bold;
// `;
    
    let PageLink = styled.a.attrs({
        title: props => {
            return 'go to ' + props.count;
        }
    })`
        color: ${props => props.isCurrentPage ? 'blue' : ''};
        cursor:pointer;
        margin-left:1rem;
        margin-right:1rem;
    `;
    
    let PaginationInfoContainer = styled.div`
        display:inline-block;
        min-width:170px;
    `;
    
    let FooterComponent = React.createClass({
        getPageLinksFromPaginationData: function(limit, page, total, pageLinkCount) {
            var maxPages = Math.ceil(total / limit);
            var pageLinks = [];
            var firstPageLink = page - Math.floor(pageLinkCount / 2);
            var lastPageLink = page + Math.floor(pageLinkCount / 2);
            if (firstPageLink < 1) {
                firstPageLink = 1;
                lastPageLink = (pageLinkCount <= maxPages ? pageLinkCount : maxPages);
            } else if (lastPageLink >= maxPages) {
                firstPageLink = (maxPages > pageLinkCount ? maxPages - pageLinkCount : 1);
                lastPageLink = maxPages;
            }
            for (var count = firstPageLink; count <= lastPageLink; count++) {
                pageLinks.push(
                    <PageLink
                        page={count}
                        isCurrentPage={this.props.pagination.page === count}
                        onClick={this.props.actions.changePage.bind(this, count)}
                    >
                        {count}
                    </PageLink>
                );
            }
            return pageLinks;
        },
        render: function() {
            var firstPage = 1;
            var lastRecord = this.props.pagination.page * this.props.pagination.limit;
            var firstRecord = lastRecord - this.props.pagination.limit + 1;
            if (lastRecord > this.props.pagination.total) {
                lastRecord = this.props.pagination.total;
            }
            if (firstRecord < 1) {
                firstRecord = 1;
            }
            var maxPages = Math.ceil(this.props.pagination.total / this.props.pagination.limit);
            return (
                <div id="product-pagination-container">
                    <div className="
                        u-padding-none
                        u-padding-left-small
                        u-border-none
                        u-background-none"
                         id="product-pagination"
                         style={{
                             justifyContent: 'center',
                             alignItems: 'center'
                         }}
                    >
                        
                        <PaginationInfoContainer>
                            Showing <span className="first-record">{firstRecord}</span> to <span
                            className="last-record">{lastRecord}</span> of
                            <span className="total-records">{this.props.pagination.total}</span>
                        </PaginationInfoContainer>
                        
                        <div className="dataTables_paginate paging_full_numbers u-inline-block u-margin-left-small">
                            <a onClick={this.props.actions.changePage.bind(this, firstPage)}
                               className={"first " + (this.props.pagination.page === firstPage ? 'paginate_active' : 'paginate_button')}>First</a>
                            
                            <span className="pagination-page-links">
                                {this.getPageLinksFromPaginationData(this.props.pagination.limit, this.props.pagination.page, this.props.pagination.total, 5)}
                            </span>
                            
                            <a onClick={this.props.actions.changePage.bind(this, maxPages)}
                               className={"last " + (this.props.pagination.page === maxPages ? 'paginate_active' : 'paginate_button') + ' u-margin-left-small'}>Last</a>
                        </div>
                        
                        
                        <StyledLink2>StyledLink2 </StyledLink2>
                        <StyledLink1>StyledLink1</StyledLink1>

                    </div>
                </div>
            );
        }
    });
    
    return FooterComponent;
    
    {/*<StyledLimitSelect*/
    }
    {/*options={[50, 100, 150, 200]}*/
    }
    {/*changeLimit={this.props.actions.changeLimit}*/
    }
    {/*limit={this.props.pagination.limit}*/
    }
    {/*/>*/
    }
    
    {/*<StyledLimitSelect*/
    }
    {/*options={[50, 100, 150, 200]}*/
    }
    {/*changeLimit={this.props.actions.changeLimit}*/
    }
    {/*limit={this.props.pagination.limit}*/
    }
    {/*>*/
    }
    {/*in the link*/
    }
    {/*</StyledLimitSelect>*/
    }
});
