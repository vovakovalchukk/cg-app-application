define([
    'react',
    'react-redux',
    'Redux/Actions/Todo'
], function(
    React,
    ReactRedux,
    Actions
) {
    var AddTodo = ReactRedux.connect()(function(store) {
        var input;

        return (
            <div>
                <form onSubmit={function(e) {
                    e.preventDefault();
                    if (!input.value.trim()) {
                        return;
                    }
                    store.dispatch(Actions.add(input.value));
                    input.value = '';
                }}>
                    <input ref={function(node) {
                        input = node;
                    }} />
                    <button type="submit">
                        Add Todo
                    </button>
                </form>
            </div>
        )
    });

    return AddTodo;
});