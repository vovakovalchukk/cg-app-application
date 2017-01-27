define([
    'React'
], function(
    React
) {
    "use strict";

    var FooterComponent = React.createClass({
        getPageLinksFromPaginationData: function(limit, page, total, pageLinkCount)
        {
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
                    <a className={(count == page ? 'paginate_active' : 'paginate_button')} onClick={this.props.onPageChange.bind(this, count)}>{count}</a>
                );
            }
            return pageLinks;
        },
        render: function()
        {
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
                    <div className="pagination table-footer" id="product-pagination">
                        <div className="dataTables_info">
                            Showing <span className="first-record">{firstRecord}</span> to <span className="last-record">{lastRecord}</span> of <span className="total-records">{this.props.pagination.total}</span>
                        </div>
                        <div className="dataTables_paginate paging_full_numbers">
                            <a onClick={this.props.onPageChange.bind(this, firstPage)} className={"first "+(this.props.pagination.page === firstPage ? 'paginate_active' : 'paginate_button')}>First</a>
                            <span className="pagination-page-links">
                                {this.getPageLinksFromPaginationData(this.props.pagination.limit, this.props.pagination.page, this.props.pagination.total, 5)}
                            </span>
                            <a onClick={this.props.onPageChange.bind(this, maxPages)} className={"last "+(this.props.pagination.page === maxPages ? 'paginate_active' : 'paginate_button')}>Last</a>
                        </div>
                    </div>
                </div>
            );
        }
    });

    return FooterComponent;
});