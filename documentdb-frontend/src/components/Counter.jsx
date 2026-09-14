import { useState } from "react";

function Counter() {
    let [counter, setCounter] = useState(0);
    if (counter < 0) {
        counter = 0;
    }
    return (
        <>
            <p>Counter: {counter}</p>
            <button onClick={() => setCounter(counter + 1)}>Increase</button>

            <button onClick={() => setCounter(counter - 1)}>Decrease</button>
        </>
    )
}

export default Counter;