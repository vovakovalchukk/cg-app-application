import React from 'react';
import styled from 'styled-components';
import LimitSelect from 'Product/Components/ProductList/Components/Footer/LimitSelect';
import PageLink from 'Product/Components/ProductList/Components/Footer/PageLink';
import BlockerModal from "Common/Components/BlockerModal";

const PaginationInfoContainer = styled.div`
        display:inline-block;
        min-width:170px;
    `;
const PageLinksContainer = styled.div`
        min-width:200px;
        display:inline-block;
        margin-left:1rem;
    `;

const ButtonModalSaveSort = styled.button`
        margin-left: 20px;
    `;

const ModalSaveSort = styled.form`
        padding: 1rem;
        div:nth-of-type(1) {
            display: flex;
            gap: 20px;
            justify-content: center;
            padding: 0 0 2rem 0;
            label {
                display: flex;
                gap: 5px;
                align-items: center;
                input {
                    width: 1rem;
                }
            }
        }    
        div:nth-of-type(2) {
            display: flex;
            justify-content: center;
            gap: 20px;
        }    
    `;

class FooterComponent extends React.Component {

    state = {
        modal: false,
    };

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

                    <ButtonModalSaveSort onClick={() => {
                        this.setState({
                            modal: true,
                        })
                    }}>Save current sort</ButtonModalSaveSort>

                    {
                        this.state.modal &&
                        <BlockerModal
                            headerText="Save Current Sort as Default Page View"
                            contentJsx={
                                <ModalSaveSort onSubmit={(e) => {
                                    e.preventDefault();
                                    if (e.target[0].checked) {
                                        console.log('current user');
                                    } else {
                                        console.log('all users');
                                    }

                                    // @TODO ajax request to server with "this.props.order" sort data
                                    // fetch('', {
                                    //     method: 'POST'
                                    // }).then((response) => {
                                    //     return response.json();
                                    // }).catch((error) => {
                                    //     console.log(error);
                                    // })
                                }}>
                                    <div>
                                        <label>
                                            <input type='radio' value='current' name='test'/>
                                            <span>Save for Current User</span>
                                        </label>
                                        <label>
                                            <input type='radio' value='all' name='test'/>
                                            <span>Save for All Users</span>
                                        </label>
                                    </div>
                                    <div>
                                        <button type='submit'>Save</button>
                                        <button onClick={() => {
                                            this.setState({
                                                modal: false,
                                            })
                                        }} type='button'>Cancel</button>
                                    </div>
                                </ModalSaveSort>
                            }
                        />
                    }
                </div>
            </div>
        );
    }
}

export default FooterComponent;