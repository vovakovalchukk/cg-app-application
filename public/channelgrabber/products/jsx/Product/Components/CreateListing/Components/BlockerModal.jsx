define([
    'react',
], function(
    React,
) {
    "use strict";
    var BlockerModal = React.createClass({
        getDefaultProps: function () {
            return {

            }
        },
        render: function() {
            return(
                <div className={'c-blocker-modal'}>
                    <div className={'c-blocker-modal__overlay'}>
                        <div className={'c-blocker-modal__container'}>
                            <div className={'c-blocker-modal__heading-container'}>
                                Access Listings Now
                            </div>
                            <div className={'c-blocker-modal__content-container'}>
                                <div className="c-blocker-modal__text-content">
                                    <p className={"c-blocker-modal__paragraph"}>Create multiple listings in one go from one simple interface. </p>
                                    <p className={"c-blocker-modal__paragraph"}>Generate more sales with more listings. </p>
                                </div>
                                <button className={'c-blocker-modal__cta-button'}>
                                    Add Listings To My Subscriptions
                                </button>
                                <div className={'c-blocker-modal__footer-text'}>
                                    Not sure? Contact our ecommerce specialists on 01617110248 to discuss or <a href="https://meetings.hubspot.com/sam197/cgdemo">Click Here</a> to book a demo.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }
    });

    return BlockerModal;
});