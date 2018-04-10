define([
    'react',
    'redux-form'
], function(
    React,
    ReduxForm
) {
    "use strict";

    var Field = ReduxForm.Field;

    var SearchComponent = React.createClass({
        render: function() {
            return (
                <form name={'search'}>
                    <div className={"order-form half product-container category-map-container"}>
                        <div>
                            <label>
                                <div className={"order-inputbox-holder"}>
                                    <Field
                                        name={"searchText"}
                                        component="input"
                                        type="text"
                                        placeholder="Search..."
                                    />
                                </div>
                            </label>
                        </div>
                    </div>
                </form>
            );
        }
    });

    SearchComponent =  ReduxForm.reduxForm({
        onSubmit: function(values) {
            console.log(values);
        }
    })(SearchComponent);

    return SearchComponent;
});