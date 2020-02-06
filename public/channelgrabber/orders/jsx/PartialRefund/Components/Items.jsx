import React from 'react';

const AMOUNT_MIN = 0;

const RefundItems = (props) => {

    const columns = ['Name', 'SKU', 'Maximum refund amount', 'Refund amount'];
    const {items, onAmountChange, onItemSelected} = props;

    const renderTableHeader = () => {
        return <tr>
            {columns.map((column) => {
                return <th key={column}>{column}</th>
            })}
        </tr>;
    };

    const renderRows = () => {
        return Object.keys(items).map((productId) => {
            const item = items[productId];
            return <tr key={item.id}>
                <td>{item.name}</td>
                <td>{item.sku}</td>
                <td>{item.amount}</td>
                <td>{renderAmountInput(item)}</td>
            </tr>
        });
    };

    const renderAmountInput = (item) => {
        return <div className={"u-width-120px u-margin-center safe-input-box"}>
            <input
                className={getAmountInputClassName(item)}
                type={'number'}
                min={AMOUNT_MIN}
                max={item.amount}
                step={0.1}
                id={`refund-item-${item.id}`}
                onChange={(event) => {
                    const newValue = parseInt(event.target.value);
                    if (newValue < AMOUNT_MIN) {
                        return;
                    }
                    onAmountChange(item.id, event.target.value)}
                }
                value={item.selectedAmount}
            />
        </div>
    };

    const getAmountInputClassName = (item) => {
        let className = 'u-float-none u-width-80px u-outline-none';

        if (parseFloat(item.amount) === 0) {
            className += 'safe-input-box--disabled';
        }

        if (item.selectedAmount < AMOUNT_MIN || item.selectedAmount > item.amount) {
            className += ' safe-input-box--error';
        }

        return className;
    };

    return <div className={'items-table-container u-margin-top-large'}>
        <form name={'partial-refund'}>
            <table className="u-margin-bottom-med">
                <thead>{renderTableHeader()}</thead>
                <tbody>{renderRows()}</tbody>
            </table>
        </form>
    </div>
};

export default RefundItems;
export {AMOUNT_MIN};