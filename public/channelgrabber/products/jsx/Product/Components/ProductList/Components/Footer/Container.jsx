import React from 'react';
import styled from 'styled-components';
import LimitSelect from 'Product/Components/ProductList/Components/Footer/LimitSelect';
import PageLink from 'Product/Components/ProductList/Components/Footer/PageLink';

const PaginationInfoContainer = styled.div`
        display:inline-block;
        min-width:170px;
    `;
const PageLinksContainer = styled.div`
        min-width:200px;
        display:inline-block;
        margin-left:1rem;
    `;

class FooterComponent extends React.Component {
    getPageLinksFromPaginationData = (limit, page, total, pageLinkCount) => {
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
                    count={count}
                    isCurrentPage={this.props.pagination.page === count}
                    onClick={this.props.actions.changePage.bind(this, count)}
                >
                    {count}
                </PageLink>
            );
        }
        return pageLinks;
    };

    getOptionsForLimitSelect = () => {
        let maximumToDisplay = this.props.pagination.total;
        
        let potentialOptions = [50, 100, 250, 500];
        
        let optionsForLimitSelect = [];
        for (let i = 0; i < potentialOptions.length; i++) {
            if (potentialOptions[i - 1] > maximumToDisplay) {
                break;
            }
            optionsForLimitSelect[i] = potentialOptions[i];
        }
        
        return optionsForLimitSelect;
    };

    render() {
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
                    
                    <PageLinksContainer className="dataTables_paginate paging_full_numbers">
                        <a onClick={this.props.actions.changePage.bind(this, firstPage)}
                           className={"first " + (this.props.pagination.page === firstPage ? 'paginate_active' : 'paginate_button')}>First</a>
                        
                        <span className="pagination-page-links">
                                {this.getPageLinksFromPaginationData(this.props.pagination.limit, this.props.pagination.page, this.props.pagination.total, 5)}
                            </span>

                        <a onClick={this.props.actions.changePage.bind(this, maxPages)}
                           className={"last " + (this.props.pagination.page === maxPages ? 'paginate_active' : 'paginate_button') + ' u-margin-left-small'}>Last</a>
                    </PageLinksContainer>
                    
                    <LimitSelect
                        options={this.getOptionsForLimitSelect()}
                        changeLimit={this.props.actions.changeLimit}
                        limit={this.props.pagination.limit}
                    />
                </div>
            </div>
        );
    }
}

export default FooterComponent;