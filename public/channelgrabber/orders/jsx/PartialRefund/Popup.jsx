import React, {useState} from 'react';
import PopupComponent from "Common/Components/Popup";
import SelectComponent from "Common/Components/Select";
import RefundItems, {AMOUNT_MIN} from "PartialRefund/Items";
import useItemsState from "PartialRefund/itemsState";
import partialRefundRequest from "PartialRefund/partialRefundRequest";

"use strict";

const PartialRefundPopup = (props) => {
    const {orderId, refundReasons, items} = props;

    const reasonState = useSelectState({});
    const itemsState = useItemsState(items);
    const [popupActive, setPopupActive] = useState(false);

    window.addEventListener('partialRefundPopup', () => {
        setPopupActive(true);
    });

    const renderReasonSelect = () => {
        return <div className={'u-flex-v-center'}>
            <div className={"u-width-120px u-font-large"}>Refund reason:</div>
            <div className={"reason-select"}>
                <SelectComponent
                    filterable={true}
                    autoSelectFirst={false}
                    onOptionChange={reasonState.onOptionChange}
                    options={formatRefundReasonsAsOptions()}
                    selectedOption={reasonState.selectedOption}
                    fullWidth={true}
                />
            </div>
        </div>
    };

    const formatRefundReasonsAsOptions = () => {
        return Object.keys(refundReasons).map((reason) => {
            return {
                name: reason,
                value: reason
            };
        });
    };

    const renderItems = () => {
        return <RefundItems
            items={itemsState.items}
            onAmountChange={itemsState.updateItemAmount}
            onItemSelected={itemsState.toggleItem}
        />
    };

    const onConfirmButtonPressed = () => {
        partialRefundRequest(orderId, reasonState.selectedOption, itemsState.items, setPopupActive);
    };

    const isFormValid = () => {
        if (!reasonState.selectedOption.value) {
            return false;
        }

        let isAmountValid = true;
        let selectedItemsCount = 0;
        Object.keys(itemsState.items).forEach((itemId) => {
            const item = itemsState.items[itemId];
            if (!item.selected) {
                return;
            }

            selectedItemsCount++;
            if (item.selectedAmount < AMOUNT_MIN || item.selectedAmount > item.amount) {
                isAmountValid = false;
            }
        });

        return isAmountValid && selectedItemsCount > 0;
    };

    return <PopupComponent
        initiallyActive={popupActive}
        headerText={"Partial Refund"}
        subHeaderText={"Please select which items you want to refund, their quantity and a reason for the refund."}
        yesButtonText={"Save"}
        noButtonText={"Cancel"}
        closeOnYes={false}
        onYesButtonPressed={onConfirmButtonPressed}
        yesButtonDisabled={!isFormValid()}
        onNoButtonPressed={() => {setPopupActive(false)}}
        yesButtonText={"Confirm"}
    >
        <div className={'partial-refund-container u-margin-top-large'}>
            {renderReasonSelect()}
            {renderItems()}
        </div>
    </PopupComponent>
};

export default PartialRefundPopup;

function useSelectState(initialValue) {
    const [selectedOption, setSelectedOption] = useState(initialValue);
    return {
        onOptionChange: (newValue) => {setSelectedOption(newValue)},
        selectedOption
    }
}
