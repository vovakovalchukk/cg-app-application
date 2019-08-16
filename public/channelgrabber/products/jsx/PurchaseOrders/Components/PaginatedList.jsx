import React, {useState} from "react";
import styled from 'styled-components';

const ChangePage = styled.button`
    min-width: 30px;
`;
const TotalPages = styled.span`
    margin-left: 0.5rem;
`;
const CurrentPage = styled.span`
    margin: 0.5rem;
`;

const PaginatedList = props => {
    let {items, editable, renderRow, className} = props;
    const limit = 50;

    let maxPages = Math.ceil(items.length / limit);
    let [currentPage, setCurrentPage] = useState(1);

    let pageItems = items.slice(
        (currentPage * limit) - limit,
        currentPage * limit
    );

    return (
        <div>
            <div className="product-list" disabled={!editable}>
                {pageItems.map(renderRow)}
            </div>
            {maxPages > 1 && renderPaginator()}
        </div>
    );

    function renderPaginator() {
        return (
            <div className={className}>
                <div>
                    <ChangePage onClick={decrementPage} className={'button'}>
                        Prev
                    </ChangePage>

                    <CurrentPage>{currentPage}</CurrentPage>

                    <ChangePage onClick={incrementPage} className={'button'}>
                        Next
                    </ChangePage>

                    <TotalPages>
                       of {maxPages}
                    </TotalPages>
                </div>
            </div>
        )
    }
    function incrementPage() {
        if (currentPage + 1 > maxPages) {
            return;
        }
        setCurrentPage(currentPage + 1);
    }
    function decrementPage() {
        if (currentPage - 1 < 1) {
            return;
        }
        setCurrentPage(currentPage - 1);
    }
};

export default PaginatedList;