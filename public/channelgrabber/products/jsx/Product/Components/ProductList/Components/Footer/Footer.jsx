define([
    'react'
], function(
    React
) {
    "use strict";
    
    let LimitSelect = (props) => {
      return(
          <select className={'u-margin-left-small'}>
              {props.options.map((option)=>{
                  return (<option value={option}>
                      {option}
                  </option>)
              })}
          </select>
      )
    };
    
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
                    <a className={(count == page ? 'paginate_active' : 'paginate_button') + ' u-margin-left-small'}
                       onClick={this.props.onPageChange.bind(this, count)}>{count}</a>
                );
            }
            return pageLinks;
        },
        render: function() {
            console.log('in footer render with this.props: ' , this.props);
            
            
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
                        
                        <div className=" u-inline-block">
                            Showing <span className="first-record">{firstRecord}</span> to <span
                            className="last-record">{lastRecord}</span> of
                            <span className="total-records">{this.props.pagination.total}</span>
                        </div>
                        <div className="dataTables_paginate paging_full_numbers u-inline-block u-margin-left-small">
                            <a onClick={this.props.onPageChange.bind(this, firstPage)}
                               className={"first " + (this.props.pagination.page === firstPage ? 'paginate_active' : 'paginate_button')} >First</a>
                            <span className="pagination-page-links">
                                {this.getPageLinksFromPaginationData(this.props.pagination.limit, this.props.pagination.page, this.props.pagination.total, 5)}
                            </span>
                            <a onClick={this.props.onPageChange.bind(this, maxPages)}
                               className={"last " + (this.props.pagination.page === maxPages ? 'paginate_active' : 'paginate_button')+' u-margin-left-small'}>Last</a>
                        </div>
                        
                        <LimitSelect options={[50,100,150,200]}/>
                    </div>
                </div>
            );
        }
    });
    
    return FooterComponent;
});
