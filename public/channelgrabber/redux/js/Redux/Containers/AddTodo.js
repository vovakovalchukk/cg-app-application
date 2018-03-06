define(['react', 'react-redux', 'Redux/Actions/Todo'], function (React, ReactRedux, Actions) {
    var AddTodo = ReactRedux.connect()(function (store) {
        var input;

        return React.createElement(
            'div',
            null,
            React.createElement(
                'form',
                { onSubmit: function (e) {
                        e.preventDefault();
                        if (!input.value.trim()) {
                            return;
                        }
                        store.dispatch(Actions.add(input.value));
                        input.value = '';
                    } },
                React.createElement('input', { ref: function (node) {
                        input = node;
                    } }),
                React.createElement(
                    'button',
                    { type: 'submit' },
                    'Add Todo'
                )
            )
        );
    });

    return AddTodo;
});
